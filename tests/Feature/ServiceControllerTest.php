<?php

use App\Models\Admin\Admin;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('service list links to the reference fields and keeps icon immediately after image', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = ServiceCategory::factory()->create(['name' => 'Danışmanlık']);
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.services.index'))->assertOk()->assertSee('Listele')->assertSee('Kategoriler')->assertSee('Hizmet ekle');
    $this->get(route('admin.services.create'))->assertOk()
        ->assertSeeInOrder(['name="image"', 'name="icon"', 'name="service_category_id"'], false)
        ->assertSee('Kategorisiz')->assertSee('İkonsuz')->assertSee($category->name)
        ->assertSee('name="title"', false)->assertSee('name="short_description"', false)
        ->assertSee('name="content"', false)->assertSee('name="meta_title"', false)
        ->assertSee('name="meta_keywords"', false)->assertSee('name="meta_description"', false)
        ->assertSee('name="is_active"', false);
});

test('services can be created with category image and icon independently absent', function (array $optional) {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.services.store'), [
        'title' => 'Danışmanlık', 'is_active' => '1', 'id' => 999,
        ...$optional,
    ])->assertRedirectToRoute('admin.services.index')->assertSessionHas('notification.message', 'Hizmet başarıyla eklendi.');

    $this->assertDatabaseHas('services', ['title' => 'Danışmanlık', 'service_category_id' => null, 'image' => null, 'icon' => ($optional['icon'] ?? null) ?: null, 'is_active' => true]);
    expect(Service::query()->sole()->id)->not->toBe(999);
    Storage::disk('public')->assertDirectoryEmpty('services');
})->with([
    'omitted' => [[]],
    'explicit null' => [['service_category_id' => null, 'image' => null, 'icon' => null]],
    'empty form values' => [['service_category_id' => '', 'image' => '', 'icon' => '']],
    'icon without image' => [['icon' => 'briefcase']],
]);

test('one category can contain multiple services and category deletion preserves them', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = ServiceCategory::factory()->create();
    $this->actingAs($admin, 'admin');

    foreach (['Birinci', 'İkinci'] as $title) {
        $this->post(route('admin.services.store'), ['title' => $title, 'is_active' => '1', 'service_category_id' => $category->id])
            ->assertRedirectToRoute('admin.services.index');
    }

    expect($category->services)->toHaveCount(2);
    expect($category->services->first()->category->is($category))->toBeTrue();
    $this->delete(route('admin.services.categories.destroy', $category))->assertRedirect();
    $this->assertDatabaseCount('services', 2);
    $this->assertDatabaseMissing('services', ['service_category_id' => $category->id]);
});

test('service creation rejects invalid categories icons titles and file uploads', function (array $invalid, string $field, ?string $message) {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();

    $response = $this->actingAs($admin, 'admin')->post(route('admin.services.store'), [
        'title' => 'Başlık', 'is_active' => '1', ...$invalid,
    ]);

    $response->assertSessionHasErrors($message === null ? [$field] : [$field => $message]);
    $this->assertDatabaseCount('services', 0);
    Storage::disk('public')->assertDirectoryEmpty('services');
})->with([
    'missing category' => [['service_category_id' => 999999], 'service_category_id', 'Geçerli bir hizmet kategorisi seçin.'],
    'non integer category' => [['service_category_id' => 'abc'], 'service_category_id', null],
    'unsafe icon' => [['icon' => '<script>alert(1)</script>'], 'icon', 'Listeden geçerli bir hizmet ikonu seçin.'],
    'blank title' => [['title' => '  '], 'title', 'Hizmet başlığını girin.'],
    'oversized title' => [['title' => str_repeat('a', 256)], 'title', null],
    'invalid state' => [['is_active' => 'invalid'], 'is_active', null],
    'not an image' => [fn () => ['image' => UploadedFile::fake()->create('file.txt', 1, 'text/plain')], 'image', null],
    'oversized image' => [fn () => ['image' => UploadedFile::fake()->image('large.jpg')->size(4097)], 'image', null],
]);

test('service creation stores all reference content with an image and an icon together', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $data = ['title' => 'Teknik destek', 'short_description' => 'Kısa açıklama', 'content' => 'Detaylı içerik', 'meta_title' => 'SEO başlık', 'meta_keywords' => 'destek, teknik', 'meta_description' => 'SEO açıklama', 'is_active' => '0', 'icon' => 'settings'];

    $this->actingAs($admin, 'admin')->post(route('admin.services.store'), [...$data, 'image' => UploadedFile::fake()->image('service.png')])
        ->assertRedirectToRoute('admin.services.index');

    $this->assertDatabaseHas('services', $data);
    Storage::disk('public')->assertExists(Service::query()->sole()->image);
});

test('service endpoints require the corresponding operation permission', function (string $method, string $action, string $permission) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $this->{$method}(route('admin.services.'.$action), ['title' => 'Denied', 'is_active' => '1'])->assertForbidden();
    $this->assertDatabaseCount('services', 0);
    $admin->givePermissionTo($permission);
    $response = $this->{$method}(route('admin.services.'.$action), ['title' => 'Allowed', 'is_active' => '1']);
    if ($action === 'store') {
        $response->assertRedirectToRoute('admin.services.index');
        $this->assertDatabaseHas('services', ['title' => 'Allowed']);
    } else {
        $response->assertOk();
    }
})->with([
    'list' => ['get', 'index', 'services.view'],
    'data' => ['get', 'data', 'services.view'],
    'form' => ['get', 'create', 'services.create'],
    'store' => ['post', 'store', 'services.create'],
]);

test('service menu needs both menu and view permissions', function (array $permissions, bool $visible) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();

    if ($visible) {
        $response->assertSee('Listele');
    } else {
        $response->assertDontSee('Listele');
    }
})->with([
    'none' => [[], false],
    'view only' => [['services.view'], false],
    'menu only' => [['services.menu'], false],
    'both' => [['services.menu', 'services.view'], true],
]);

test('service table searches categories escapes titles and renders icons without images', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = ServiceCategory::factory()->create(['name' => 'Teknik']);
    $service = Service::factory()->for($category, 'category')->create(['title' => '<script>alert(1)</script>', 'icon' => 'briefcase']);
    Service::factory()->create(['title' => 'Other']);

    $this->actingAs($admin, 'admin')->getJson(route('admin.services.data', [
        'draw' => 1, 'start' => 0, 'length' => 10, 'search' => ['value' => 'Teknik'],
        'columns' => [
            ['data' => 'title', 'name' => 'services.title', 'searchable' => 'true', 'orderable' => 'true'],
            ['data' => 'category_name', 'name' => 'category.name', 'searchable' => 'true', 'orderable' => 'true'],
        ],
    ]))->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', $service->id)
        ->assertJsonPath('data.0.title', e($service->title))
        ->assertJsonPath('data.0.category_name', 'Teknik')
        ->assertJsonPath('data.0.visual', fn (string $visual): bool => str_contains($visual, '<svg'));
});

test('guests and inactive accounts cannot access service creation', function () {
    $this->get(route('admin.services.create'))->assertRedirectToRoute('admin.login');
    $admin = Admin::factory()->superAdmin()->inactive()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.services.store'), ['title' => 'Blocked', 'is_active' => '1'])
        ->assertRedirectToRoute('admin.login');
    $this->assertDatabaseCount('services', 0);
});

test('disabled Services blocks new service endpoints and hides their menu', function () {
    $disk = Storage::fake('disabled-service-content');
    $disk->put('statuses.json', json_encode(['Services' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Listele');
    $this->get(route('admin.services.index'))->assertNotFound();
    $this->get(route('admin.services.create'))->assertNotFound();
    $this->getJson(route('admin.services.data'))->assertNotFound();
    $this->post(route('admin.services.store'), ['title' => 'Blocked', 'is_active' => '1'])->assertNotFound();
    $this->assertDatabaseCount('services', 0);
});

test('service feature definitions render validate and persist through the shared field infrastructure', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $disk = Storage::fake('service-feature-definitions');
    $disk->put('Services/module.json', json_encode(['name' => 'Services', 'providers' => []], JSON_THROW_ON_ERROR));
    $disk->put('Services/features.json', json_encode([
        'schema_version' => 1,
        'forms' => ['service' => ['fields' => [
            ['key' => 'caption', 'label' => 'Hizmet etiketi', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['required' => true, 'max' => 50]],
        ]]],
    ], JSON_THROW_ON_ERROR));
    $disk->put('statuses.json', json_encode(['Services' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.services.create'))->assertOk()->assertSee('name="extra_fields[caption]"', false);
    $this->post(route('admin.services.store'), ['title' => 'Eksik etiket', 'is_active' => '1'])
        ->assertSessionHasErrors(['extra_fields.caption' => 'Hizmet etiketi alanını doldurun.']);
    $this->assertDatabaseCount('services', 0);
    $this->post(route('admin.services.store'), ['title' => 'Etiketli hizmet', 'is_active' => '1', 'extra_fields' => ['caption' => 'Uzman destek']])
        ->assertRedirectToRoute('admin.services.index');
    expect(Service::query()->sole()->extra_fields)->toBe(['caption' => 'Uzman destek']);
});
