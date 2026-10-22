<?php

use App\Models\Admin\Admin;
use App\Services\Admin\Modules\ModuleManager;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Modules\Konum\Models\City;
use Modules\Konum\Services\KonumFrontendService;
use Modules\Konum\Services\KonumImportService;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

require_once __DIR__.'/../Fixtures/source.php';
require_once __DIR__.'/../../../../tests/Fixtures/module-distribution.php';

uses(LazilyRefreshDatabase::class);

test('location routes redirect guests and deny admins with only menu permission', function () {
    $this->get(route('admin.konum.index'))->assertRedirectToRoute('admin.login');
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('konum.menu');

    $this->actingAs($admin, 'admin')->get(route('admin.konum.index'))->assertForbidden();
    $this->getJson(route('admin.konum.data', 'cities'))->assertForbidden();
});

test('location data is unavailable until import completes and rejects unbounded requests', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->withoutVite();
    $this->get(route('admin.konum.index'))->assertOk()->assertSee('henüz kullanıma hazır değil');
    $this->getJson(route('admin.konum.data', 'cities'))->assertStatus(409);
    $this->getJson(route('admin.konum.data', ['entity' => 'cities', 'length' => -1]))->assertUnprocessable();
    $this->getJson(route('admin.konum.data', ['entity' => 'streets', 'city_id' => 'invalid']))->assertUnprocessable();
});

test('location data filters parents and omits geometry and cached child lists', function () {
    app(KonumImportService::class)->import(konumFixtureSource());
    City::factory()->create(['id' => 2, 'name' => '<script>alert(1)</script>', 'is_consistent' => true]);
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('konum.view');
    $this->actingAs($admin, 'admin');

    $this->getJson(route('admin.konum.data', ['entity' => 'towns', 'city_id' => 1, 'length' => 25]))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Aladağ');
    $this->getJson(route('admin.konum.data', ['entity' => 'towns', 'city_id' => 2, 'length' => 25]))
        ->assertOk()->assertJsonCount(0, 'data');
    $cities = $this->getJson(route('admin.konum.data', ['entity' => 'cities', 'length' => 25]))
        ->assertOk()->assertJsonCount(2, 'data')->assertDontSee('<script>', false);
    expect($cities->json('data.0'))->not->toHaveKeys(['polygons', 'towns', 'source_row_hash']);
    $this->withoutVite();
    $this->get(route('admin.konum.index', ['entity' => 'neighbourhoods', 'city_id' => 1, 'town_id' => 1, 'district_id' => 1]))
        ->assertOk()->assertSee('Mahalleler')->assertSee('konum-records-table');
});

test('disabled location module blocks super admins and frontend consumers', function () {
    app(KonumImportService::class)->import(konumFixtureSource());
    $disk = Storage::fake('konum-status');
    $disk->put('statuses.json', '{"Konum":false}');
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), base_path('Modules')));
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.konum.index'))->assertNotFound();
    $this->getJson(route('admin.konum.data', 'cities'))->assertNotFound();
    expect(app(KonumFrontendService::class)->search('cities'))->toBeEmpty();
});

test('disabling reactivating and removing location code preserve imported data', function () {
    $report = app(KonumImportService::class)->import(konumFixtureSource());
    prepareDistribution();
    $metadata = json_decode(file_get_contents(__DIR__.'/../../module.json'), true);
    putDistributionModule('Konum', true, ['panel' => $metadata['panel']]);
    Process::preventStrayProcesses();
    Process::fake(function ($process) {
        if (in_array('db:seed', $process->command, true)) {
            Artisan::call('db:seed', ['--class' => 'Modules\\Konum\\Database\\Seeders\\KonumInstallSeeder', '--force' => true, '--no-interaction' => true]);
        }

        return Process::result();
    });
    $manager = app(ModuleManager::class);

    $manager->disable('Konum', 1);
    expect(Module::findOrFail('Konum')->isEnabled())->toBeFalse();
    $manager->enable('Konum', 1);
    expect(Module::findOrFail('Konum')->isEnabled())->toBeTrue();
    Process::assertRanTimes(fn ($process): bool => in_array('db:seed', $process->command, true), 1);
    Process::assertNotRan(fn ($process): bool => in_array('konum:import', $process->command, true));
    $manager->remove('Konum', 1);

    expect(Module::find('Konum'))->toBeNull();
    $this->assertDatabaseCount('konum_streets', 1);
    expect(json_decode(DB::table('konum_imports')->value('report'), true))->toBe($report);
    expect(DB::table('konum_imports')->value('status'))->toBe('complete');
});
