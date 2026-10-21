<?php

use App\Jobs\InstallQueuedModule;
use App\Models\Admin\Admin;
use App\Services\Admin\Modules\ModuleManager;
use App\Services\Admin\Modules\ModuleInstallations;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/../Fixtures/module-distribution.php';

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Http::preventStrayRequests();
    Process::preventStrayProcesses();
});

test('customer admins cannot access any module management action even with an assigned matching permission', function (string $method, string $route, array $parameters) {
    $admin = Admin::factory()->create();
    $permission = Permission::findOrCreate('manage-modules', 'admin');
    $admin->givePermissionTo($permission);
    prepareDistribution();

    $this->actingAs($admin, 'admin')->{$method}(route($route, $parameters))->assertForbidden();

    Http::assertNothingSent();
    Process::assertNothingRan();
})->with([
    ['get', 'admin.modules.index', []], ['get', 'admin.modules.create', []],
    ['get', 'admin.modules.show', ['sample']], ['post', 'admin.modules.store', []],
    ['post', 'admin.modules.enable', ['Sample']], ['post', 'admin.modules.disable', ['Sample']],
    ['delete', 'admin.modules.destroy', ['Sample']],
]);

test('guests are redirected to login before module management runs', function () {
    prepareDistribution();

    $this->get(route('admin.modules.index'))->assertRedirectToRoute('admin.login');

    Http::assertNothingSent();
    Process::assertNothingRan();
});

test('super admins see escaped installed cards and unconfigured catalog without a nonfunctional install button', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    putDistributionModule(changes: ['description' => '<script>alert(1)</script>']);
    config(['module-distribution.catalog_url' => null]);

    $this->actingAs($admin, 'admin')->get(route('admin.modules.index'))->assertOk()
        ->assertSee('Sample')->assertSee('1.2.3')->assertSee('Etkin')
        ->assertDontSee('<script>alert(1)</script>', false);
    $this->get(route('admin.modules.create'))->assertOk()->assertSee('Modül kataloğu henüz bağlanmadı')
        ->assertDontSee('Seçilen modülleri kur');

    Http::assertNothingSent();
});

test('catalog cards support searching details and bulk checkboxes while deriving installed state locally', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    $entry = distributionEntry();
    $entry['description'] = '<script>alert(1)</script>';
    Http::fake([config('module-distribution.catalog_url') => Http::response(['schema_version' => 1, 'modules' => [$entry]])]);
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.modules.create', ['search' => 'Örnek']))->assertOk()
        ->assertSee('Örnek Modül')->assertSee('name="modules[]"', false)->assertDontSee('<script>alert(1)</script>', false);
    $this->get(route('admin.modules.show', 'sample'))->assertOk()->assertSee('Gerekli modüller');
    $this->get(route('admin.modules.create', ['search' => 'absent']))->assertOk()->assertDontSee('Örnek Modül');
    putDistributionModule();
    $this->get(route('admin.modules.create'))->assertOk()->assertSee('Kurulu · Etkin')->assertDontSee('Seçilen modülleri kur');

    Http::assertSentCount(4);
});

test('module disable and enable persist state and record operations', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    putDistributionModule();
    Process::fake();
    Queue::fake();
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.modules.disable', 'Sample'))->assertRedirectToRoute('admin.modules.index')
        ->assertSessionHas('notification.type', 'success');
    expect(Module::findOrFail('Sample')->isEnabled())->toBeFalse();
    $this->post(route('admin.modules.enable', 'Sample'))->assertRedirectToRoute('admin.modules.index')
        ->assertSessionHas('notification.type', 'success');

    expect(Module::findOrFail('Sample')->isEnabled())->toBeFalse();
    $installations = app(ModuleInstallations::class);
    $id = $installations->latest()['id'];
    $installations->claim($id, 0);
    $this->artisan('module:install-queued', ['installation' => $id, 'index' => 0])->assertSuccessful();
    $installations->finish($id, 0);

    expect(Module::findOrFail('Sample')->isEnabled())->toBeTrue();
    expect(app(ModuleManager::class)->history())->toHaveCount(2);
    Queue::assertPushed(InstallQueuedModule::class, 1);
    Process::assertRan(fn ($process): bool => in_array('migrate', $process->command, true) && in_array('--realpath', $process->command, true));
});

test('dependent modules prevent disabling or removing a required module', function (bool $enabled, string $action, bool $blocked) {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    putDistributionModule();
    putDistributionModule('Dependent', $enabled, ['requires' => ['sample' => '^1.0']]);
    Process::fake();
    $this->actingAs($admin, 'admin');

    $response = $action === 'disable'
        ? $this->post(route('admin.modules.disable', 'Sample'))
        : $this->delete(route('admin.modules.destroy', 'Sample'));

    $response->assertRedirectToRoute('admin.modules.index')->assertSessionHas('notification.type', $blocked ? 'error' : 'success');
    expect(Module::findOrFail('Sample')->isEnabled())->toBe($blocked);
    if ($blocked) {
        Process::assertNothingRan();
    } else {
        Process::assertRan(fn ($process): bool => in_array('optimize:clear', $process->command, true));
    }
})->with([
    [true, 'disable', true], [true, 'remove', true],
    [false, 'disable', false], [false, 'remove', true],
]);

test('removal moves code to a recoverable backup without rolling back migrations or deleting application data', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    putDistributionModule();
    Process::fake();

    $this->actingAs($admin, 'admin')->delete(route('admin.modules.destroy', 'Sample'))
        ->assertRedirectToRoute('admin.modules.index')->assertSessionHas('notification.type', 'success');

    expect(Module::find('Sample'))->toBeNull();
    $this->assertModelExists($admin);
    expect(glob(app(ModuleManager::class)->workingPath().'/removed-Sample-*/module.json'))->toHaveCount(1);
    Process::assertNotRan(fn ($process): bool => in_array('module:migrate-rollback', $process->command, true));
});

test('bulk installation revalidates catalog and queues the selected fixture through the endpoint', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    $entry = distributionEntry();
    Http::fake([
        config('module-distribution.catalog_url') => Http::response(['schema_version' => 1, 'modules' => [$entry]]),
    ]);
    Process::fake();
    Queue::fake();

    $this->actingAs($admin, 'admin')->post(route('admin.modules.store'), ['modules' => ['sample']])
        ->assertRedirectToRoute('admin.modules.index')->assertSessionHas('notification.type', 'success');

    expect(Module::find('Sample'))->toBeNull();
    $this->assertDatabaseHas('module_installations', ['actor' => $admin->id, 'status' => 'queued']);
    Queue::assertPushed(InstallQueuedModule::class, 1);
    Http::assertSentCount(1);
    Process::assertNothingRan();
});

test('installation requires selected module IDs and refuses client supplied download identities', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();

    $this->actingAs($admin, 'admin')->postJson(route('admin.modules.store'), ['modules' => ['../escape']])
        ->assertUnprocessable()->assertJsonValidationErrors('modules.0');

    Http::assertNothingSent();
    Process::assertNothingRan();
});

test('failed removal restores module code in a disabled state and retains a failed operation', function () {
    $admin = Admin::factory()->superAdmin()->create();
    prepareDistribution();
    putDistributionModule();
    Process::fake(fn () => Process::result(exitCode: 1));

    $this->actingAs($admin, 'admin')->delete(route('admin.modules.destroy', 'Sample'))
        ->assertRedirectToRoute('admin.modules.index')->assertSessionHas('notification.type', 'error');

    Storage::disk('module-distribution')->assertExists('Modules/Sample/module.json');
    expect(Module::findOrFail('Sample')->isEnabled())->toBeFalse();
    expect(app(ModuleManager::class)->history()[0]['status'])->toBe('failed');
    Process::assertRan(fn ($process): bool => in_array('dump-autoload', $process->command, true));
});
