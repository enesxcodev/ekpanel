<?php

use App\Models\Admin\Admin;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\LogViewer\Services\LogFileBrowserService;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $this->logsRoot = storage_path('framework/testing/logviewer-'.Str::uuid());
    app(Filesystem::class)->ensureDirectoryExists($this->logsRoot);
    $this->app->instance(LogFileBrowserService::class, new LogFileBrowserService($this->logsRoot));
});

afterEach(function (): void {
    app(Filesystem::class)->deleteDirectory($this->logsRoot);
});

it('shows the newest log file and only its requested tail to a super admin', function () {
    $lines = array_map(static fn (int $line): string => sprintf('entry-%03d', $line), range(1, 300));
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'laravel.log', implode("\n", $lines));

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.logviewer.index', ['file' => 'laravel.log', 'lines' => 100]))
        ->assertOk()
        ->assertSee('laravel.log')
        ->assertSee('entry-201')
        ->assertSee('entry-300')
        ->assertDontSee('entry-200');
});

it('renders log content as escaped text', function () {
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'laravel.log', '<script>alert(1)</script>');

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.logviewer.index', ['file' => 'laravel.log']))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('forbids customer admins from viewing log contents even when the view ability is granted', function () {
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'laravel.log', 'private log entry');
    Gate::define('logviewer.view', static fn (): bool => true);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.logviewer.index'))
        ->assertForbidden();
});

it('redirects guests to the admin login page', function () {
    $this->get(route('admin.logviewer.index'))->assertRedirectToRoute('admin.login');
});

it('returns not found for traversal and non-log paths', function (string $path) {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.logviewer.index', ['file' => $path]))
        ->assertNotFound();
})->with(['../outside.log', 'folder/../../outside.log', 'laravel.txt', 'C:/secret.log']);

it('limits tail requests to the supported line counts', function () {
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'laravel.log', 'entry');
    $service = new LogFileBrowserService($this->logsRoot);

    expect($service->readTail('laravel.log', 99))->toBeNull();
});

it('bounds the displayed content from a single oversized log line', function () {
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'laravel.log', str_repeat('x', 2_000_000));
    $service = new LogFileBrowserService($this->logsRoot);

    $tail = $service->readTail('laravel.log', 200);

    expect($tail)->not->toBeNull()
        ->and(strlen($tail['lines'][0]))->toBeLessThanOrEqual(1_048_576)
        ->and($tail['truncated'])->toBeTrue();
});

it('lists valid log files but excludes hidden files and unrelated extensions', function () {
    app(Filesystem::class)->ensureDirectoryExists($this->logsRoot.DIRECTORY_SEPARATOR.'daily');
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'laravel.log', 'current');
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'daily'.DIRECTORY_SEPARATOR.'worker-2026-10-23.log', 'daily');
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'.secret.log', 'hidden');
    file_put_contents($this->logsRoot.DIRECTORY_SEPARATOR.'debug.txt', 'ignored');
    $service = new LogFileBrowserService($this->logsRoot);

    expect(array_column($service->files(), 'path'))->toBe(['daily/worker-2026-10-23.log', 'laravel.log']);
});

it('does not follow a symbolic link outside the log directory', function () {
    $outsideRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'logviewer-outside-'.Str::uuid();
    app(Filesystem::class)->ensureDirectoryExists($outsideRoot);
    file_put_contents($outsideRoot.DIRECTORY_SEPARATOR.'private.log', 'outside secret');
    $link = $this->logsRoot.DIRECTORY_SEPARATOR.'linked';

    if (! @symlink($outsideRoot, $link)) {
        app(Filesystem::class)->deleteDirectory($outsideRoot);
        $this->markTestSkipped('The current operating system does not allow creating a symbolic link.');
    }

    $service = new LogFileBrowserService($this->logsRoot);

    expect($service->files())->toBe([])
        ->and($service->readTail('linked/private.log', 200))->toBeNull();

    app(Filesystem::class)->deleteDirectory($outsideRoot);
});
