<?php

use App\Models\Admin\Admin;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

/** @param array<string, mixed> $menuOverrides */
function registerPanelTestModule(bool $enabled = true, array $menuOverrides = []): void
{
    $disk = Storage::fake('panel-test-modules');
    $disk->put('Services/module.json', json_encode([
        'name' => 'Services',
        'providers' => [],
        'panel' => [
            'permissions' => ['services.menu', 'services.view'],
        ],
    ], JSON_THROW_ON_ERROR));
    config(['services.menus' => ['services' => array_replace([
        'label' => 'Test Hizmetler Menüsü',
        'route' => 'admin.test-services.index',
        'permission' => 'services.view',
        'menu_permission' => 'services.menu',
        'order' => 10,
    ], $menuOverrides)]]);
    $disk->put('statuses.json', json_encode(['Services' => $enabled], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));

    Route::middleware(['web', 'auth:admin', 'module.enabled:Services', 'can:services.view'])
        ->get('/admin/test-services', fn (): string => 'Services content')
        ->name('admin.test-services.index');
    Route::getRoutes()->refreshNameLookups();
}

test('module sidebar visibility requires both menu and page permissions', function (array $permissions, bool $visible) {
    registerPanelTestModule();
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();

    if ($visible) {
        $response->assertSee('Test Hizmetler Menüsü');
    } else {
        $response->assertDontSee('Test Hizmetler Menüsü');
    }
})->with([
    'neither permission' => [[], false],
    'page permission only' => [['services.view'], false],
    'menu permission only' => [['services.menu'], false],
    'both permissions' => [['services.view', 'services.menu'], true],
]);

test('super admins only see enabled module menus', function (bool $enabled) {
    registerPanelTestModule($enabled);
    $admin = Admin::factory()->superAdmin()->create();

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();

    if ($enabled) {
        $response->assertSee('Test Hizmetler Menüsü');
    } else {
        $response->assertDontSee('Test Hizmetler Menüsü');
    }
})->with([true, false]);

test('invalid menu declarations are hidden even from super admins', function (array $menuOverrides) {
    registerPanelTestModule(menuOverrides: $menuOverrides);
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))
        ->assertOk()->assertDontSee('Test Hizmetler Menüsü');
})->with([
    'missing menu permission' => [['menu_permission' => null]],
    'undeclared menu permission' => [['menu_permission' => 'services.unknown']],
    'missing route' => [['route' => 'admin.missing.index']],
]);

test('hiding a module menu does not remove permitted direct page access', function () {
    registerPanelTestModule();
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('services.view');

    $this->actingAs($admin, 'admin')->get(route('admin.test-services.index'))
        ->assertOk()->assertSee('Services content');
});

test('menu permission alone does not grant module page access', function () {
    registerPanelTestModule();
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('services.menu');

    $this->actingAs($admin, 'admin')->get(route('admin.test-services.index'))->assertForbidden();
});

test('disabled modules refuse direct access even for super admins', function () {
    registerPanelTestModule(false);
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.test-services.index'))->assertNotFound();
});

test('permission synchronization is repeatable and preserves role grants', function () {
    registerPanelTestModule();
    $panel = app(ModulePanelService::class);
    $panel->syncPermissions();
    $role = Role::findOrCreate('customer-admin', 'admin');
    $role->givePermissionTo('services.view');

    $this->artisan('module:permissions')->assertSuccessful();

    $this->assertDatabaseCount('permissions', 2);
    $this->assertDatabaseHas('permissions', ['name' => 'services.menu', 'guard_name' => 'admin']);
    expect($role->fresh()->permissions->pluck('name')->all())->toBe(['services.view']);
});

test('menu groups keep permitted children and hide unauthorized siblings', function () {
    registerPanelTestModule();
    $disk = Storage::disk('panel-test-modules');
    $manifest = json_decode($disk->get('Services/module.json'), true, flags: JSON_THROW_ON_ERROR);
    $manifest['panel']['permissions'] = ['services.menu', 'services.view', 'services.categories.menu', 'services.categories.view'];
    $disk->put('Services/module.json', json_encode($manifest, JSON_THROW_ON_ERROR));
    $leaf = config('services.menus.services');
    config(['services.menus' => [
        'services' => [
            'label' => 'Test Hizmetler Grubu',
            'children' => [
                'list' => [...$leaf, 'label' => 'Test Hizmet Listesi'],
                'categories' => [...$leaf, 'label' => 'Test Kategoriler', 'permission' => 'services.categories.view', 'menu_permission' => 'services.categories.menu'],
            ],
        ],
    ]]);
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['services.categories.menu', 'services.categories.view']);

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Test Hizmetler Grubu')->assertSee('Test Kategoriler')->assertDontSee('Test Hizmet Listesi');
});
