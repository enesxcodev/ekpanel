<?php

use App\Jobs\InstallQueuedModule;
use App\Models\Admin\Admin;
use App\Services\Admin\Modules\ModuleInstallations;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

require_once __DIR__.'/../Fixtures/module-distribution.php';

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('q', 32))]);
});

test('installation request queues one durable job and returns without downloading or installing', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    $entry = distributionEntry();
    Http::preventStrayRequests();
    Http::fake([config('module-distribution.catalog_url') => Http::response(['schema_version' => 1, 'modules' => [$entry]])]);
    Process::preventStrayProcesses();
    Queue::fake();

    $this->actingAs($admin, 'admin')->post(route('admin.modules.store'), ['modules' => ['sample']])
        ->assertRedirectToRoute('admin.modules.index')->assertSessionHas('notification.type', 'success');

    $this->assertDatabaseHas('module_installations', ['actor' => $admin->id, 'status' => 'queued', 'active_key' => 'installation']);
    Queue::assertPushed(InstallQueuedModule::class, fn ($job): bool => $job->index === 0 && $job->connection === 'module-installations');
    Queue::assertPushed(InstallQueuedModule::class, 1);
    Storage::disk('module-distribution')->assertMissing('Modules/Sample');
    Http::assertSentCount(1);
    Process::assertNothingRan();
});

test('duplicate requests do not create another active installation', function () {
    prepareDistribution();
    Queue::fake();
    $service = app(ModuleInstallations::class);
    $service->enqueue([distributionEntry()], 7);

    expect(fn () => $service->enqueue([distributionEntry()], 7))->toThrow(RuntimeException::class, 'zaten sırada');

    $this->assertDatabaseCount('module_installations', 1);
    Queue::assertPushed(InstallQueuedModule::class, 1);
});

test('a queued installation exposes truthful durable progress after a page reload', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    Queue::fake();
    $this->withoutVite();
    $id = app(ModuleInstallations::class)->enqueue([distributionEntry()], $admin->id);

    $this->actingAs($admin, 'admin')->get(route('admin.modules.index'))->assertOk()->assertSee('data-module-installation', false)
        ->assertSee('kuyruk çalışanının işi alması bekleniyor');
    $this->getJson(route('admin.modules.installations.show', $id))->assertOk()
        ->assertJsonPath('status', 'queued')->assertJsonPath('percentage', 0)->assertJsonPath('results.0.status', 'queued');

    Queue::assertPushed(InstallQueuedModule::class, 1);
});

test('customers cannot read installation progress or dispatch installations', function (string $method) {
    $admin = Admin::factory()->create();
    prepareDistribution();
    Queue::fake();

    $this->actingAs($admin, 'admin')->{$method}($method === 'getJson'
        ? route('admin.modules.installations.show', '00000000-0000-0000-0000-000000000001')
        : route('admin.modules.store'), ['modules' => ['sample']])->assertForbidden();

    Queue::assertNothingPushed();
})->with(['getJson', 'post']);

test('dependency failures skip dependent modules while independent installations continue', function () {
    prepareDistribution();
    Queue::fake();
    $sample = distributionEntry();
    $dependent = array_replace($sample, ['id' => 'dependent', 'name' => 'Dependent', 'requires' => ['modules' => ['sample' => '^1.0']]]);
    $other = array_replace($sample, ['id' => 'other', 'name' => 'Other']);
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([$dependent, $sample, $other], 7);
    expect(array_column($service->find($id)['results'], 'id'))->toBe(['sample', 'other', 'dependent']);
    $service->claim($id, 0);
    $service->progress($id, 0, 4, 'Composer hazırlanıyor');

    $service->finish($id, 0, 'Composer başarısız');
    $service->claim($id, 1);
    $service->finish($id, 1);

    $status = $service->find($id);
    expect($status['status'])->toBe('failed');
    expect($status['percentage'])->toBeLessThan(100);
    expect(array_column($status['results'], 'status'))->toBe(['failed', 'succeeded', 'skipped']);
    expect($status['results'][0]['stage'])->toBe('Composer hazırlanıyor');
    Queue::assertPushed(InstallQueuedModule::class, 2);
});

test('duplicate job delivery runs only one fresh artisan process and never repeats installation', function () {
    prepareDistribution();
    Queue::fake();
    Process::preventStrayProcesses();
    Process::fake();
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([distributionEntry()], 7);
    $job = new InstallQueuedModule($id, 0);

    $job->handle($service);
    $job->handle($service);

    expect($service->find($id)['status'])->toBe('succeeded');
    expect($service->find($id)['percentage'])->toBe(100);
    Process::assertRanTimes(fn ($process): bool => in_array('module:install-queued', $process->command, true) && $process->timeout === 3300, 1);
    Queue::assertPushed(InstallQueuedModule::class, 1);
});

test('process failures preserve their last stage and remain below complete progress', function () {
    prepareDistribution();
    Queue::fake();
    Process::preventStrayProcesses();
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([distributionEntry()], 7);
    Process::fake(function () use ($service, $id) {
        $service->progress($id, 0, 6, 'Migration uygulanıyor');

        return Process::result(errorOutput: 'migration error', exitCode: 1);
    });

    (new InstallQueuedModule($id, 0))->handle($service);

    expect($service->find($id))->toMatchArray(['status' => 'failed', 'percentage' => 50]);
    expect($service->find($id)['results'][0])->toMatchArray(['stage' => 'Migration uygulanıyor', 'status' => 'failed']);
    expect($service->find($id)['results'][0]['error'])->toContain('migration error');
    Process::assertRanTimes(fn ($process): bool => in_array('module:install-queued', $process->command, true), 1);
    Queue::assertPushed(InstallQueuedModule::class, 1);
});

test('an interrupted worker becomes a terminal failure without retrying partial installation', function () {
    prepareDistribution();
    Queue::fake();
    $this->freezeTime();
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([distributionEntry()], 7);
    $service->claim($id, 0);
    $service->progress($id, 0, 4, 'Composer hazırlanıyor');

    $this->travel(61)->minutes();
    $status = $service->find($id);

    expect($status['status'])->toBe('failed');
    expect($status['results'][0]['error'])->toContain('Otomatik tekrar yapılmadı');
    expect($service->claim($id, 0))->toBeFalse();
    $this->assertDatabaseHas('module_installations', ['id' => $id, 'active_key' => null]);
    Queue::assertPushed(InstallQueuedModule::class, 1);
});

test('database transport serializes and executes an installation job on its dedicated queue', function () {
    prepareDistribution();
    Process::preventStrayProcesses();
    Process::fake();
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([distributionEntry()], 7);
    $this->assertDatabaseHas('jobs', ['queue' => 'module-installations']);

    Queue::connection('module-installations')->pop('module-installations')->fire();

    expect($service->find($id)['status'])->toBe('succeeded');
    $this->assertDatabaseCount('jobs', 0);
    Process::assertRanTimes(fn ($process): bool => in_array('module:install-queued', $process->command, true), 1);
});

test('cyclic dependencies are rejected before a queued installation is created', function () {
    prepareDistribution();
    Queue::fake();
    $entry = distributionEntry();
    $entry['requires']['modules'] = ['sample' => '^1.0'];

    expect(fn () => app(ModuleInstallations::class)->enqueue([$entry], 7))->toThrow(RuntimeException::class, 'döngüsel');

    $this->assertDatabaseCount('module_installations', 0);
    Queue::assertNothingPushed();
});

test('the queued artisan command uses the existing installer and persists the actual failing stage', function (bool $fails) {
    prepareDistribution();
    Queue::fake();
    $bytes = distributionZip();
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::preventStrayRequests();
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::preventStrayProcesses();
    Process::fake(fn ($process) => Process::result(exitCode: $fails && in_array('migrate', $process->command, true) ? 1 : 0));
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([$entry], 7);
    $service->claim($id, 0);

    $this->artisan('module:install-queued', ['installation' => $id, 'index' => 0])->assertExitCode($fails ? 1 : 0);

    expect($service->find($id)['results'][0]['completed'])->toBe($fails ? 6 : 11);
    expect($service->find($id)['percentage'])->toBeLessThan(100);
    expect(json_decode(Storage::disk('module-distribution')->get('statuses.json'), true)['Sample'])->toBe(! $fails);
    Http::assertSentCount(1);
    Process::assertRan(fn ($process): bool => in_array('migrate', $process->command, true));
    Queue::assertPushed(InstallQueuedModule::class, 1);
})->with([false, true]);

test('queue storage failure rolls back the durable installation instead of leaving it permanently queued', function () {
    prepareDistribution();
    config(['queue.connections.module-installations.table' => 'unavailable_queue_table']);

    expect(fn () => app(ModuleInstallations::class)->enqueue([distributionEntry()], 7))->toThrow(QueryException::class);

    $this->assertDatabaseCount('module_installations', 0);
});

test('inactive super admins cannot inspect progress', function () {
    $admin = Admin::factory()->superAdmin()->create(['status' => false]);

    $this->actingAs($admin, 'admin')->getJson(route('admin.modules.installations.show', '00000000-0000-0000-0000-000000000001'))->assertRedirectToRoute('admin.login');
});

test('guests must log in before inspecting installation progress', function () {
    $this->get(route('admin.modules.installations.show', '00000000-0000-0000-0000-000000000001'))->assertRedirectToRoute('admin.login');
});

test('worker failure before claiming the queued record releases its active installation slot', function () {
    prepareDistribution();
    Queue::fake();
    $service = app(ModuleInstallations::class);
    $id = $service->enqueue([distributionEntry()], 7);

    (new InstallQueuedModule($id, 0))->failed(new RuntimeException('Worker could not start'));

    expect($service->find($id)['status'])->toBe('failed');
    $this->assertDatabaseHas('module_installations', ['id' => $id, 'active_key' => null]);
    expect($service->find($id)['results'][0]['error'])->toContain('Worker could not start');
    Queue::assertPushed(InstallQueuedModule::class, 1);
});
