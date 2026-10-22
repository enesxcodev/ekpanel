<?php

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardService;
use App\Services\Admin\Dashboard\DashboardWidgetRegistry;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Modules\WidgetFixture\Dashboard\Widgets\CountWidget;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    config(['app.key' => 'base64:'.base64_encode(str_repeat('w', 32))]);
});

/** @param list<string> $classes */
function registerDashboardFixture(bool $enabled = true, array $classes = ['CountWidget', 'SecondaryWidget', 'BrokenWidget'], string $source = 'config/widgets.php'): void
{
    $disk = Storage::fake('dashboard-fixture-modules');
    $disk->put('WidgetFixture/module.json', json_encode([
        'name' => 'WidgetFixture', 'alias' => 'widgetfixture', 'providers' => [],
        'panel' => ['widgets' => $source, 'permissions' => ['widgetfixture.view', 'widgetfixture.secondary.view']],
    ], JSON_THROW_ON_ERROR));
    foreach (['CountWidget', 'SecondaryWidget', 'BrokenWidget', 'DuplicateWidget'] as $class) {
        $target = 'WidgetFixture/app/Dashboard/Widgets/'.$class.'.php';
        $disk->put($target, file_get_contents(base_path('tests/Fixtures/dashboard/'.$class.'.php')));
        require_once $disk->path($target);
    }
    $classNames = array_map(fn (string $class): string => 'Modules\\WidgetFixture\\Dashboard\\Widgets\\'.$class, $classes);
    $disk->put('WidgetFixture/config/widgets.php', '<?php return '.var_export($classNames, true).';');
    $disk->put('outside.php', '<?php throw new RuntimeException("Source escaped");');
    $disk->put('statuses.json', json_encode(['WidgetFixture' => $enabled], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    CountWidget::$calls = 0;
}

function dashboardFixtureAdmin(array $permissions = ['widgetfixture.view']): Admin
{
    $admin = Admin::factory()->create();
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'admin');
    }
    $admin->givePermissionTo($permissions);

    return $admin;
}

test('dashboard metadata only exposes authorized widgets and does not execute their data queries', function () {
    registerDashboardFixture();
    $admin = dashboardFixtureAdmin();

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();

    $response->assertViewHas('widgetDashboard', fn (array $state): bool => $state['selected'] === ['widgetfixture.total', 'widgetfixture.broken'] && ! isset($state['definitions']['widgetfixture.secondary']));
    $response->assertDontSee('Yeni Form Başvuruları');
    expect(CountWidget::$calls)->toBe(0);
});

test('all installed module widget data uses valid models fields and named routes', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $state = app(DashboardService::class)->state($admin);
    expect($state['definitions'])->not->toBeEmpty();

    $response = $this->actingAs($admin, 'admin')->postJson(route('admin.dashboard.widgets.data'), ['widget_ids' => array_keys($state['definitions'])])->assertOk();

    foreach ($response->json('widgets') as $widget) {
        expect($widget['status'], $widget['id'])->toBe('ready');
    }
});

test('selected data is loaded in a batch and one failing widget does not replace another count', function () {
    registerDashboardFixture();
    $this->actingAs(dashboardFixtureAdmin(), 'admin');

    $response = $this->postJson(route('admin.dashboard.widgets.data'), ['widget_ids' => ['widgetfixture.total', 'widgetfixture.broken']])
        ->assertOk()->assertJsonPath('widgets.0.status', 'ready')->assertJsonPath('widgets.1.status', 'error');

    expect($response->json('widgets.0.html'))->toContain('Fixture total');
    expect($response->json('widgets.1.html'))->toContain('Veri yüklenemedi', 'Yeniden dene')->not->toContain('Private database detail', 'dashboard-widget-value');
    expect(CountWidget::$calls)->toBe(1);
});

test('unauthorized data requests are rejected before any widget query executes', function () {
    registerDashboardFixture();
    $this->actingAs(dashboardFixtureAdmin(), 'admin');

    $this->postJson(route('admin.dashboard.widgets.data'), ['widget_ids' => ['widgetfixture.total', 'widgetfixture.secondary']])
        ->assertUnprocessable()->assertJsonValidationErrors('widget_order');

    expect(CountWidget::$calls)->toBe(0);
});

test('saved empty selections remain empty and preferences belong to the authenticated admin', function () {
    registerDashboardFixture();
    $first = dashboardFixtureAdmin();
    $other = dashboardFixtureAdmin();
    $this->actingAs($first, 'admin')->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => [], 'revision' => 0])
        ->assertOk()->assertJsonPath('widget_order', [])->assertJsonPath('revision', 1);

    expect(app(DashboardService::class)->state($first)['selected'])->toBe([]);
    expect(app(DashboardService::class)->state($other)['selected'])->toContain('widgetfixture.total');
    $this->assertDatabaseHas('admin_dashboard_preferences', ['admin_id' => $first->id, 'widget_order' => '[]', 'revision' => 1]);
    $this->assertDatabaseMissing('admin_dashboard_preferences', ['admin_id' => $other->id]);
});

test('saved order survives permission loss and module disable then reappears when access returns', function () {
    registerDashboardFixture();
    $admin = dashboardFixtureAdmin(['widgetfixture.view', 'widgetfixture.secondary.view']);
    $this->actingAs($admin, 'admin')->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => ['widgetfixture.secondary', 'widgetfixture.total'], 'revision' => 0])
        ->assertOk()->assertJsonPath('widget_order', ['widgetfixture.secondary', 'widgetfixture.total']);
    $admin->revokePermissionTo('widgetfixture.secondary.view');
    $this->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => [], 'revision' => 1])->assertOk();
    $this->assertDatabaseHas('admin_dashboard_preferences', ['admin_id' => $admin->id, 'widget_order' => '["widgetfixture.secondary"]']);
    Module::find('WidgetFixture')->disable();

    expect(app(DashboardService::class)->state($admin)['selected'])->toBe([]);
    $this->postJson(route('admin.dashboard.widgets.data'), ['widget_ids' => ['widgetfixture.total']])->assertUnprocessable();
    expect(CountWidget::$calls)->toBe(0);
    Module::find('WidgetFixture')->enable();
    $admin->givePermissionTo('widgetfixture.secondary.view');
    expect(app(DashboardService::class)->state($admin)['selected'])->toBe(['widgetfixture.secondary']);
});

test('a new module widget is available without being automatically added to an existing personal layout', function () {
    registerDashboardFixture(classes: ['CountWidget']);
    $admin = dashboardFixtureAdmin();
    app(DashboardService::class)->save($admin, ['widgetfixture.total'], 0);
    registerDashboardFixture();

    $state = app(DashboardService::class)->state($admin);

    expect($state['definitions'])->toHaveKey('widgetfixture.broken');
    expect($state['selected'])->toBe(['widgetfixture.total']);
});

test('stale and invalid first revisions return 409 without overwriting the stored layout', function () {
    registerDashboardFixture();
    $admin = dashboardFixtureAdmin();
    $this->actingAs($admin, 'admin')->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => ['widgetfixture.total'], 'revision' => 4])->assertConflict();
    $this->assertDatabaseMissing('admin_dashboard_preferences', ['admin_id' => $admin->id]);
    $this->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => ['widgetfixture.total'], 'revision' => 0])->assertOk();

    $this->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => [], 'revision' => 0])->assertConflict();

    $this->assertDatabaseHas('admin_dashboard_preferences', ['admin_id' => $admin->id, 'widget_order' => '["widgetfixture.total"]', 'revision' => 1]);
    $this->assertDatabaseCount('admin_dashboard_preferences', 1);
});

test('invalid selections are rejected without creating a preference', function (array $payload) {
    registerDashboardFixture();
    $this->actingAs(dashboardFixtureAdmin(), 'admin');

    $this->putJson(route('admin.dashboard.preferences.update'), $payload)->assertUnprocessable();

    $this->assertDatabaseCount('admin_dashboard_preferences', 0);
})->with([
    'duplicates' => [['widget_order' => ['widgetfixture.total', 'widgetfixture.total'], 'revision' => 0]],
    'unknown' => [['widget_order' => ['widgetfixture.missing'], 'revision' => 0]],
    'unauthorized' => [['widget_order' => ['widgetfixture.secondary'], 'revision' => 0]],
    'other admin' => [['widget_order' => [], 'revision' => 0, 'admin_id' => 999]],
    'object order' => [['widget_order' => ['key' => 'widgetfixture.total'], 'revision' => 0]],
    'missing order' => [['revision' => 0]],
    'missing revision' => [['widget_order' => []]],
    'too many' => [['widget_order' => array_fill(0, 201, 'widgetfixture.total'), 'revision' => 0]],
]);

test('disabled modules are absent even for super admins', function () {
    registerDashboardFixture(false);
    $admin = Admin::factory()->superAdmin()->create();

    expect(app(DashboardService::class)->state($admin)['definitions'])->toBe([]);
});

test('an uninstalled module is hidden without executing its widget and its saved selection is retained', function () {
    registerDashboardFixture();
    $admin = dashboardFixtureAdmin();
    app(DashboardService::class)->save($admin, ['widgetfixture.total'], 0);
    $disk = Storage::disk('dashboard-fixture-modules');
    $disk->delete('WidgetFixture/module.json');
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));

    $state = app(DashboardService::class)->state($admin);
    expect($state['definitions'])->toBe([]);
    expect($state['selected'])->toBe([]);
    $this->actingAs($admin, 'admin')->postJson(route('admin.dashboard.widgets.data'), ['widget_ids' => ['widgetfixture.total']])->assertUnprocessable();
    expect(CountWidget::$calls)->toBe(0);
    app(DashboardService::class)->save($admin, [], 1);
    $this->assertDatabaseHas('admin_dashboard_preferences', ['admin_id' => $admin->id, 'widget_order' => '["widgetfixture.total"]']);
});

test('oversized widget payloads return 413 without writing a preference', function () {
    registerDashboardFixture();
    $this->actingAs(dashboardFixtureAdmin(), 'admin');

    $this->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => [str_repeat('x', 33000)], 'revision' => 0])->assertStatus(413);

    $this->assertDatabaseCount('admin_dashboard_preferences', 0);
});

test('duplicate identities and invalid classes do not overwrite valid registrations', function () {
    registerDashboardFixture(classes: ['CountWidget', 'DuplicateWidget', 'MissingWidget', 'SecondaryWidget']);

    $widgets = app(DashboardWidgetRegistry::class)->all();

    expect(array_keys($widgets))->toBe(['widgetfixture.secondary']);
});

test('widget source cannot escape the module directory', function () {
    registerDashboardFixture(source: '../outside.php');

    expect(app(DashboardWidgetRegistry::class)->all())->toBe([]);
});

test('the module widget generator registers a real model count without a core dashboard edit', function () {
    registerDashboardFixture();
    $module = Module::find('WidgetFixture');
    $disk = Storage::disk('dashboard-fixture-modules');
    $disk->put('WidgetFixture/app/Models/FixtureRecord.php', '<?php namespace Modules\\WidgetFixture\\Models; class FixtureRecord extends \\Illuminate\\Database\\Eloquent\\Model { protected $table = "admins"; }');
    require_once $disk->path('WidgetFixture/app/Models/FixtureRecord.php');
    Route::get('/widget-fixture-records', fn (): string => 'Records')->name('admin.widgetfixture.index');
    Route::getRoutes()->refreshNameLookups();
    $admin = dashboardFixtureAdmin();

    $this->artisan('module:make-dashboard-widget', [
        'module' => 'WidgetFixture', 'model' => 'FixtureRecord', '--title' => 'Fixture records', '--id' => 'widgetfixture.records.count', '--default' => true,
    ])->assertSuccessful();
    require_once $module->getPath().'/app/Dashboard/Widgets/FixtureRecordCountWidget.php';

    $state = app(DashboardService::class)->state($admin);
    expect($state['selected'])->toContain('widgetfixture.records.count');
    $data = app(DashboardService::class)->data($admin, ['widgetfixture.records.count']);
    expect($data[0]['status'])->toBe('ready');
    expect($data[0]['html'])->toContain('Fixture records', 'dashboard-widget-value');
});

test('the generator refuses models owned by the application instead of the target module', function () {
    registerDashboardFixture();

    $this->artisan('module:make-dashboard-widget', ['module' => 'WidgetFixture', 'model' => Admin::class, '--title' => 'Unsafe count'])->assertFailed();

    expect(file_exists(Module::find('WidgetFixture')->getPath().'/app/Dashboard/Widgets/AdminCountWidget.php'))->toBeFalse();
});

test('dashboard widget endpoints redirect guests and inactive admins to login', function () {
    $this->postJson(route('admin.dashboard.widgets.data'), ['widget_ids' => []])->assertUnauthorized();
    $this->actingAs(Admin::factory()->inactive()->create(), 'admin')
        ->putJson(route('admin.dashboard.preferences.update'), ['widget_order' => [], 'revision' => 0])->assertRedirectToRoute('admin.login');
});
