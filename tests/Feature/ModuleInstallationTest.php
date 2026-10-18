<?php

use App\Services\Admin\Modules\ModuleArchive;
use App\Services\Admin\Modules\ModuleCatalog;
use App\Services\Admin\Modules\ModuleCompatibility;
use App\Services\Admin\Modules\ModuleManager;
use App\Services\Admin\Modules\ModulePanelAssets;
use App\Services\Admin\Modules\ModuleRelease;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Facades\Module;

require_once __DIR__.'/../Fixtures/module-distribution.php';

beforeEach(function () {
    Http::preventStrayRequests();
    Process::preventStrayProcesses();
});

test('validated release installs resolves dependencies migrates and records success without default seeding', function () {
    prepareDistribution();
    $bytes = distributionZip();
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::fake();

    app(ModuleManager::class)->install($entry, 7);

    expect(Module::findOrFail('Sample')->isEnabled())->toBeTrue();
    Storage::disk('module-distribution')->assertExists('Modules/Sample/composer.json');
    expect(app(ModuleManager::class)->history()[0])->toMatchArray(['status' => 'success', 'action' => 'install', 'actor' => 7]);
    Http::assertSentCount(1);
    Process::assertRan(fn ($process): bool => in_array('update', $process->command, true) && in_array('laravel/framework', $process->command, true));
    Process::assertRan(fn ($process): bool => in_array('migrate', $process->command, true) && in_array('--realpath', $process->command, true));
    Process::assertRan(fn ($process): bool => in_array('module:permissions', $process->command, true));
    Process::assertNotRan(fn ($process): bool => in_array('db:seed', $process->command, true) || in_array('module:seed', $process->command, true));
});

test('actual Menus release installs from its ZIP and publishes the declared assets without a customer npm build', function () {
    $menus = Module::findOrFail('Menus');
    prepareDistribution();
    $disk = Storage::disk('module-distribution');
    $releaseService = app(ModuleRelease::class);
    $release = $releaseService->package($menus, $disk->path('releases'));
    $entry = $releaseService->catalogEntry($menus, $release, 'https://modules.example.test/Menus.zip');
    $entry['requires']['modules'] = (array) $entry['requires']['modules'];
    Http::fake([$entry['download']['url'] => Http::response(file_get_contents($release['path']))]);
    Process::fake();

    app(ModuleManager::class)->install($entry, 7);

    expect(Module::findOrFail('Menus')->isEnabled())->toBeTrue();
    expect(app(ModulePanelAssets::class)->forRoute('admin.menus.index')['js'])->toHaveCount(1);
    $disk->assertExists('Modules/Menus/tests/Feature/MenuWorkspaceTest.php');
    $disk->assertMissing('Modules/Menus/backups');
    Process::assertNotRan(fn ($process): bool => in_array('npm', $process->command, true));
    Http::assertSentCount(1);
});

test('wrong archive hash is rejected before extraction or commands and records failure', function () {
    prepareDistribution();
    $entry = distributionEntry();
    Http::fake([$entry['download']['url'] => Http::response('wrong bytes')]);
    Process::fake();

    expect(fn () => app(ModuleManager::class)->install($entry, 7))->toThrow(RuntimeException::class, 'SHA-256');

    Storage::disk('module-distribution')->assertMissing('Modules/Sample');
    expect(app(ModuleManager::class)->history()[0]['status'])->toBe('failed');
    Process::assertNothingRan();
    Http::assertSentCount(1);
});

test('archive extraction rejects traversal absolute windows and reserved file names', function (string $path) {
    prepareDistribution();
    $disk = Storage::disk('module-distribution');
    distributionZip([$path => 'unsafe']);

    expect(fn () => app(ModuleArchive::class)->extract($disk->path('fixture.zip'), $disk->path('extracted'), distributionEntry()))
        ->toThrow(RuntimeException::class, 'Güvensiz ZIP');

    $disk->assertMissing('extracted/module.json');
})->with(['../escape.php', '/absolute.php', 'C:/escape.php', 'folder/../../escape.php', 'folder\\escape.php', 'CON.php', 'folder/file.']);

test('mismatched archive metadata cannot enter the modules directory', function () {
    prepareDistribution();
    $bytes = distributionZip(['composer.json' => '{"name":"wrong/package"}']);
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::fake();

    expect(fn () => app(ModuleManager::class)->install($entry, 7))->toThrow(RuntimeException::class, 'katalogla eşleşmiyor');

    Storage::disk('module-distribution')->assertMissing('Modules/Sample');
    Process::assertNothingRan();
    Http::assertSentCount(1);
});

test('failed external command leaves module installed disabled and records a recoverable failure', function () {
    prepareDistribution();
    $bytes = distributionZip();
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::fake(fn () => Process::result(exitCode: 1));

    expect(fn () => app(ModuleManager::class)->install($entry, 7))->toThrow(RuntimeException::class, 'devre dışı tutuldu');

    expect(Module::findOrFail('Sample')->isEnabled())->toBeFalse();
    expect(app(ModuleManager::class)->history()[0]['status'])->toBe('failed');
    Http::assertSentCount(1);
    Process::assertRanTimes(fn ($process): bool => in_array('update', $process->command, true), 1);
});

test('catalog rejects schema mismatch duplicate identities unsafe hosts and invalid hashes', function (string $kind) {
    prepareDistribution();
    $entry = distributionEntry();
    $document = ['schema_version' => 1, 'modules' => [$entry]];
    if ($kind === 'schema') {
        $document['schema_version'] = 2;
    } elseif ($kind === 'duplicate') {
        $document['modules'][] = $entry;
    } elseif ($kind === 'host') {
        $document['modules'][0]['download']['url'] = 'https://localhost/internal.zip';
    } else {
        $document['modules'][0]['download']['sha256'] = 'bad-hash';
    }
    Http::fake([config('module-distribution.catalog_url') => Http::response($document)]);

    expect(fn () => app(ModuleCatalog::class)->all())->toThrow(RuntimeException::class);

    Http::assertSentCount(1);
})->with(['schema', 'duplicate', 'host', 'hash']);

test('download redirects to untrusted hosts are blocked before the second request', function () {
    prepareDistribution();
    Http::fake([config('module-distribution.catalog_url') => Http::response('', 302, ['Location' => 'http://127.0.0.1/private'])]);

    expect(fn () => app(ModuleCatalog::class)->all())->toThrow(RuntimeException::class, 'HTTPS');

    Http::assertSentCount(1);
});

test('incompatible platform or missing module dependency fails before downloading', function (string $kind) {
    prepareDistribution();
    $entry = distributionEntry();
    if ($kind === 'platform') {
        $entry['requires']['php'] = '^99.0';
    } else {
        $entry['requires']['modules'] = ['missing' => '^1.0'];
    }
    Process::fake();

    expect(fn () => app(ModuleManager::class)->install($entry, 7))->toThrow(RuntimeException::class);

    Http::assertNothingSent();
    Process::assertNothingRan();
})->with(['platform', 'dependency']);

test('supported compatibility constraints accept only matching versions', function (string $version, string $constraint, bool $matches) {
    expect(app(ModuleCompatibility::class)->matches($version, $constraint))->toBe($matches);
})->with([
    ['13.36.0', '^13.0', true], ['14.0.0', '^13.0', false],
    ['8.4.14', '>=8.4 <9.0', true], ['8.3.0', '>=8.4 <9.0', false],
    ['1.2.9', '~1.2.3', true], ['1.3.0', '~1.2.3', false],
    ['0.2.0', '^0.1.0', false], ['13.1.0', '^12.0 || ^13.0', true],
    ['0.9.0', '^0', true], ['0.1.0', '^0.0', false],
]);

test('only declared installation seeders run and a failed seeder is retried when activating the pending install', function () {
    prepareDistribution();
    $manifest = [
        'name' => 'Sample', 'alias' => 'sample', 'version' => '1.2.3', 'providers' => [], 'requires' => [],
        'panel' => ['install' => ['seeders' => ['Modules\\Sample\\Database\\Seeders\\InstallSeeder']]],
    ];
    $bytes = distributionZip(['module.json' => json_encode($manifest, JSON_THROW_ON_ERROR)]);
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::fake(fn ($process) => Process::result(exitCode: in_array('db:seed', $process->command, true) ? 1 : 0));

    expect(fn () => app(ModuleManager::class)->install($entry, 7))->toThrow(RuntimeException::class, 'devre dışı tutuldu');

    expect(Module::findOrFail('Sample')->isEnabled())->toBeFalse();
    Storage::disk('module-distribution')->assertExists('management/pending-Sample.json');
    Process::assertRan(fn ($process): bool => in_array('--class=Modules\\Sample\\Database\\Seeders\\InstallSeeder', $process->command, true));

    Process::fake();
    app(ModuleManager::class)->enable('Sample', 7);

    expect(Module::findOrFail('Sample')->isEnabled())->toBeTrue();
    Storage::disk('module-distribution')->assertMissing('management/pending-Sample.json');
    Process::assertRan(fn ($process): bool => in_array('--class=Modules\\Sample\\Database\\Seeders\\InstallSeeder', $process->command, true));
    Http::assertSentCount(1);
});

test('archive symbolic links and expanded size limits are rejected before extraction', function (string $kind) {
    prepareDistribution();
    $disk = Storage::disk('module-distribution');
    distributionZip(['app/link.php' => '../../outside']);
    if ($kind === 'symlink') {
        $zip = new ZipArchive;
        $zip->open($disk->path('fixture.zip'));
        $zip->setExternalAttributesName('app/link.php', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        $zip->close();
    } else {
        config(['module-distribution.max_unpacked_bytes' => 1]);
    }

    expect(fn () => app(ModuleArchive::class)->extract($disk->path('fixture.zip'), $disk->path('extracted'), distributionEntry()))
        ->toThrow(RuntimeException::class);

    $disk->assertMissing('extracted/module.json');
})->with(['symlink', 'size']);

test('management lock refuses concurrent operations without starting external commands', function () {
    prepareDistribution();
    $manager = app(ModuleManager::class);
    mkdir($manager->workingPath(), 0777, true);
    $lock = fopen($manager->workingPath().'/management.lock', 'c');
    flock($lock, LOCK_EX);
    Process::fake();

    try {
        expect(fn () => $manager->install(distributionEntry(), 7))->toThrow(RuntimeException::class, 'Başka bir modül işlemi');
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    Http::assertNothingSent();
    Process::assertNothingRan();
});
