<?php

use App\Models\Admin\Admin;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Services\Models\ServiceCategory;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

test('super admins can manage service categories from the panel', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.services.categories.index'))->assertOk()
        ->assertSee('Hizmetler')->assertSee('Kategoriler')->assertSee('Kategori ekle');
    $this->get(route('admin.services.categories.create'))->assertOk()->assertSee('name="name"', false);
    $this->post(route('admin.services.categories.store'), ['name' => 'Danışmanlık'])
        ->assertRedirectToRoute('admin.services.categories.index')
        ->assertSessionHas('notification.message', 'Hizmet kategorisi başarıyla eklendi.');
    $this->assertDatabaseHas('service_categories', ['name' => 'Danışmanlık']);
    $category = ServiceCategory::query()->sole();

    $this->get(route('admin.services.categories.edit', $category))->assertOk()->assertSee('Danışmanlık');
    $this->put(route('admin.services.categories.update', $category), ['name' => 'Teknik Danışmanlık', 'id' => 999])
        ->assertRedirectToRoute('admin.services.categories.index')
        ->assertSessionHas('notification.type', 'success');
    $this->assertDatabaseHas('service_categories', ['id' => $category->id, 'name' => 'Teknik Danışmanlık']);
    expect($category->fresh()->getAttributes())->toHaveKeys(['id', 'name', 'extra_fields', 'sort_order', 'is_active'])->toHaveCount(5);

    $this->delete(route('admin.services.categories.destroy', $category))
        ->assertRedirectToRoute('admin.services.categories.index')
        ->assertSessionHas('notification.message', 'Hizmet kategorisi başarıyla silindi.');
    $this->assertModelMissing($category);
});

test('category endpoints require their own operation permission', function (string $method, string $action, string $permission, bool $granted) {
    $this->seed(AdminPermissionSeeder::class);
    $category = ServiceCategory::factory()->create(['name' => 'Existing category']);
    $admin = Admin::factory()->create();
    $role = Role::findByName('customer-admin', 'admin');
    if ($granted) {
        $role->givePermissionTo($permission);
    }
    $admin->assignRole($role);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $category : [];

    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.services.categories.'.$action, $parameters), ['name' => 'Changed category']);

    if (! $granted) {
        $response->assertForbidden();
        $this->assertDatabaseCount('service_categories', 1);
        $this->assertDatabaseHas('service_categories', ['id' => $category->id, 'name' => 'Existing category']);
    } elseif (in_array($action, ['store', 'update', 'destroy'], true)) {
        $response->assertRedirectToRoute('admin.services.categories.index');
        if ($action === 'destroy') {
            $this->assertModelMissing($category);
        } else {
            $this->assertDatabaseHas('service_categories', ['name' => 'Changed category']);
        }
    } else {
        $response->assertOk();
    }
})->with([
    'list' => ['get', 'index', 'services.categories.view'],
    'table data' => ['get', 'data', 'services.categories.view'],
    'create form' => ['get', 'create', 'services.categories.create'],
    'store' => ['post', 'store', 'services.categories.create'],
    'edit form' => ['get', 'edit', 'services.categories.update'],
    'update' => ['put', 'update', 'services.categories.update'],
    'delete' => ['delete', 'destroy', 'services.categories.delete'],
])->with([true, false]);

test('guests are redirected from service categories to the admin login', function () {
    $this->get(route('admin.services.categories.index'))->assertRedirectToRoute('admin.login');
});

test('inactive super admins cannot access service categories', function () {
    $admin = Admin::factory()->superAdmin()->inactive()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.services.categories.index'))
        ->assertRedirectToRoute('admin.login');
});

test('category names are validated on creation and update', function (string $action, mixed $name, string $message) {
    $admin = Admin::factory()->superAdmin()->create();
    $category = ServiceCategory::factory()->create(['name' => 'Original']);
    $parameters = $action === 'update' ? $category : [];
    $method = $action === 'update' ? 'put' : 'post';

    $this->actingAs($admin, 'admin')->{$method}(route('admin.services.categories.'.$action, $parameters), ['name' => $name])
        ->assertSessionHasErrors(['name' => $message]);

    $this->assertDatabaseCount('service_categories', 1);
    $this->assertDatabaseHas('service_categories', ['id' => $category->id, 'name' => 'Original']);
})->with(['store', 'update'])->with([
    'blank' => ['   ', 'Kategori adını girin.'],
    'non-string' => [['invalid'], 'Geçerli bir kategori adı girin.'],
    'too long' => [str_repeat('a', 256), 'Kategori adı en fazla 255 karakter olabilir.'],
]);

test('category table supports searching and escapes names while showing permitted actions', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['services.categories.view', 'services.categories.update']);
    $category = ServiceCategory::factory()->create(['name' => '<script>alert("xss")</script>']);
    ServiceCategory::factory()->create(['name' => 'Other category']);

    $this->actingAs($admin, 'admin')->getJson(route('admin.services.categories.data', [
        'draw' => 1, 'start' => 0, 'length' => 10,
        'search' => ['value' => 'script'],
        'columns' => [
            ['data' => 'id', 'name' => 'id', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true'],
        ],
    ]))->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 1)
        ->assertJsonPath('data.0.id', $category->id)
        ->assertJsonPath('data.0.name', e($category->name))
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});

test('category menu is visible only with both menu and view permissions', function (array $permissions, bool $visible) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
    $categoryLink = 'href="'.route('admin.services.categories.index').'"';

    if ($visible) {
        $response->assertSee($categoryLink, false)->assertSee('Kategoriler');
    } else {
        $response->assertDontSee($categoryLink, false);
    }
})->with([
    'none' => [[], false],
    'view only' => [['services.categories.view'], false],
    'menu only' => [['services.categories.menu'], false],
    'both' => [['services.categories.menu', 'services.categories.view'], true],
]);

test('role editor displays category permissions under a readable module label', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $role = Role::findByName('customer-admin', 'admin');

    $this->actingAs($admin, 'admin')->get(route('admin.roles.edit', $role))
        ->assertOk()->assertSee('Hizmetler / Kategoriler')
        ->assertSee('value="services.categories.menu"', false)
        ->assertSee('value="services.categories.delete"', false);

    expect($role->fresh()->permissions->pluck('name')->all())->toContain('admins.menu');
    expect($role->fresh()->permissions->pluck('name')->all())->not->toContain('services.categories.menu');
});

test('disabled services module hides its group and blocks cached routes for super admins', function () {
    $disk = Storage::fake('disabled-services');
    $disk->put('statuses.json', json_encode(['Services' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk()->assertDontSee('Hizmetler');
    $this->get(route('admin.services.categories.index'))->assertNotFound();
});
