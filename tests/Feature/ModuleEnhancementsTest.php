<?php

use App\Models\Admin\Admin;
use App\Models\Settings\SettingDefinition;
use App\Services\Settings\SettingsService;
use Database\Seeders\AdminPermissionSeeder;
use Database\Seeders\Settings\SettingsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Models\Brand;
use Modules\Menus\Models\Menu;
use Modules\Menus\Services\NavigationService;
use Modules\Pages\Models\Page;
use Modules\Sector\Models\Sector;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;
use Modules\Slider\Models\Slider;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('services can be edited and deleted while preserving then cleaning their image', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.services.store'), [
        'title' => 'Hizmet', 'slug' => 'hizmet', 'sort_order' => 3, 'is_active' => 1,
        'image' => UploadedFile::fake()->image('service.jpg'),
    ])->assertRedirectToRoute('admin.services.index');
    $service = Service::query()->sole();
    $image = $service->image;

    $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('value="hizmet"', false);
    $this->put(route('admin.services.update', $service), [
        'title' => 'Yeni hizmet', 'slug' => 'hizmet', 'sort_order' => 1, 'is_active' => 0,
        'content' => '<p>İçerik</p><script>alert(1)</script>',
    ])->assertRedirectToRoute('admin.services.index');

    $this->assertDatabaseHas('services', ['id' => $service->id, 'slug' => 'hizmet', 'sort_order' => 1, 'is_active' => false, 'image' => $image, 'content' => '<p>İçerik</p>']);
    Storage::disk('public')->assertExists($image);

    $this->delete(route('admin.services.destroy', $service))->assertRedirectToRoute('admin.services.index');
    $this->assertModelMissing($service);
    Storage::disk('public')->assertMissing($image);
});

test('service management fields accept null and reject invalid or duplicate slugs', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $service = Service::factory()->create(['slug' => 'existing']);
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.services.store'), ['title' => 'Yeni', 'is_active' => 1, 'slug' => null, 'sort_order' => null])
        ->assertRedirectToRoute('admin.services.index');
    $this->assertDatabaseHas('services', ['title' => 'Yeni', 'slug' => null, 'sort_order' => null]);
    $this->post(route('admin.services.store'), ['title' => 'Hatalı', 'is_active' => 1, 'slug' => $service->slug])
        ->assertSessionHasErrors('slug');
    $this->post(route('admin.services.store'), ['title' => 'Hatalı', 'is_active' => 1, 'slug' => 'Test Başlık', 'sort_order' => -1])
        ->assertSessionHasErrors(['slug', 'sort_order']);
    $this->assertDatabaseMissing('services', ['title' => 'Hatalı']);
});

test('service editing and deletion enforce their new permissions', function (string $action) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $service = Service::factory()->create();
    $this->actingAs($admin, 'admin');

    if ($action === 'update') {
        $this->get(route('admin.services.edit', $service))->assertForbidden();
        $this->put(route('admin.services.update', $service), ['title' => 'Denied', 'is_active' => 1])->assertForbidden();
        $admin->givePermissionTo('services.update');
        $this->get(route('admin.services.edit', $service))->assertOk();
        $this->put(route('admin.services.update', $service), ['title' => 'Allowed', 'is_active' => 1])->assertRedirectToRoute('admin.services.index');
        $this->assertDatabaseHas('services', ['id' => $service->id, 'title' => 'Allowed']);
    } else {
        $this->delete(route('admin.services.destroy', $service))->assertForbidden();
        $this->assertModelExists($service);
        $admin->givePermissionTo('services.delete');
        $this->delete(route('admin.services.destroy', $service))->assertRedirectToRoute('admin.services.index');
        $this->assertModelMissing($service);
    }
})->with(['update', 'delete']);

test('service categories save optional ordering and default to active', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.services.categories.store'), ['name' => 'Yeni kategori'])
        ->assertRedirectToRoute('admin.services.categories.index');
    $category = ServiceCategory::query()->sole();
    expect($category->is_active)->toBeTrue();

    $this->put(route('admin.services.categories.update', $category), ['name' => 'Yeni kategori', 'sort_order' => 2, 'is_active' => 0])
        ->assertRedirectToRoute('admin.services.categories.index');
    $this->assertDatabaseHas('service_categories', ['id' => $category->id, 'sort_order' => 2, 'is_active' => false]);
});

test('page SEO fields persist through create and update and appear in the public page head', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $response = $this->actingAs($admin, 'admin')->post(route('admin.pages.store'), [
        'title' => 'Kurumsal', 'slug' => 'kurumsal', 'status' => 'published', 'sort_order' => 0,
        'meta_title' => 'Özel başlık', 'meta_keywords' => 'kurumsal', 'meta_description' => 'Özel açıklama',
    ]);
    $page = Page::query()->sole();
    $response->assertRedirectToRoute('admin.pages.edit', ['page' => $page->id, 'lang' => 'tr']);
    $this->assertDatabaseHas('pages', ['id' => $page->id, 'meta_title' => 'Özel başlık', 'meta_description' => 'Özel açıklama']);
    $this->get('/kurumsal')->assertOk()->assertSee('<title>Özel başlık</title>', false)
        ->assertSee('<meta name="description" content="Özel açıklama">', false);

    $this->put(route('admin.pages.update', $page), ['title' => 'Kurumsal', 'slug' => 'kurumsal', 'status' => 'published', 'sort_order' => 0, 'meta_title' => null, 'meta_description' => null])
        ->assertRedirectToRoute('admin.pages.edit', ['page' => $page->id, 'lang' => 'tr']);
    $this->assertDatabaseHas('pages', ['id' => $page->id, 'meta_title' => null, 'meta_description' => null]);
    $this->get('/kurumsal')->assertOk()->assertSee('Kurumsal ·', false);
});

test('SEO validation errors reopen the SEO tab in page and sector forms', function (string $module) {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');
    $url = route('admin.'.$module.'.create');
    $data = ['title' => 'Başlık', 'slug' => 'baslik', 'sort_order' => 0, 'status' => 'published', 'is_active' => 1, 'meta_title' => str_repeat('a', 256)];

    $this->from($url)->post(route('admin.'.$module.'.store'), $data)->assertRedirect($url)->assertSessionHasErrors('meta_title');
    $this->get($url)->assertOk()->assertSee('id="'.($module === 'pages' ? 'page' : 'sector').'-form-seo" role="tabpanel"', false)
        ->assertSee('class="tab-pane active show"', false);
})->with(['pages', 'sector']);

test('sector slug and ordering accept null preserve their own slug and reject duplicates', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.sector.store'), ['title' => 'Sektör', 'is_active' => 1, 'slug' => null, 'sort_order' => null])
        ->assertRedirectToRoute('admin.sector.index');
    $sector = Sector::query()->sole();
    $this->assertDatabaseHas('sectors', ['id' => $sector->id, 'slug' => null, 'sort_order' => null]);

    $data = ['title' => 'Sektör', 'is_active' => 1, 'slug' => 'test-baslik', 'sort_order' => 2, 'content' => '<h2>Başlık</h2><script>alert(1)</script>'];
    $this->put(route('admin.sector.update', $sector), $data)->assertRedirectToRoute('admin.sector.index');
    $this->put(route('admin.sector.update', $sector), $data)->assertRedirectToRoute('admin.sector.index');
    $this->assertDatabaseHas('sectors', ['id' => $sector->id, 'slug' => 'test-baslik', 'sort_order' => 2, 'content' => '<h2>Başlık</h2>']);
    $this->post(route('admin.sector.store'), $data)->assertSessionHasErrors('slug');
    $this->post(route('admin.sector.store'), [...$data, 'slug' => 'Test Başlık', 'sort_order' => -1])->assertSessionHasErrors(['slug', 'sort_order']);
    $this->assertDatabaseCount('sectors', 1);
});

test('sliders default to active and can be made passive', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.slider.store'), ['title' => 'Slider', 'image' => UploadedFile::fake()->image('slide.jpg')])
        ->assertRedirectToRoute('admin.slider.index');
    $slider = Slider::query()->sole();
    expect($slider->is_active)->toBeTrue();

    $this->put(route('admin.slider.update', $slider), ['title' => 'Slider', 'is_active' => 0])->assertRedirectToRoute('admin.slider.index');
    expect($slider->fresh()->is_active)->toBeFalse();
    $this->put(route('admin.slider.update', $slider), ['title' => 'Slider', 'is_active' => 'invalid'])->assertSessionHasErrors('is_active');
    expect($slider->fresh()->is_active)->toBeFalse();
});

test('brands default to active and save nullable ordering and passive status', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.brand.store'), ['name' => 'Marka', 'sort_order' => null, 'image' => UploadedFile::fake()->image('brand.png')])
        ->assertRedirectToRoute('admin.brand.index');
    $brand = Brand::query()->sole();
    expect($brand->is_active)->toBeTrue();
    expect($brand->sort_order)->toBeNull();

    $this->put(route('admin.brand.update', $brand), ['name' => 'Marka', 'sort_order' => 5, 'is_active' => 0])->assertRedirectToRoute('admin.brand.index');
    $this->assertDatabaseHas('brands', ['id' => $brand->id, 'sort_order' => 5, 'is_active' => false]);
    $this->put(route('admin.brand.update', $brand), ['name' => 'Marka', 'sort_order' => -1, 'is_active' => 'invalid'])->assertSessionHasErrors(['sort_order', 'is_active']);
    $this->assertDatabaseHas('brands', ['id' => $brand->id, 'sort_order' => 5, 'is_active' => false]);
});

test('menu and nested item icons can be saved cleared and consumed in navigation', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.menus.store'), ['name' => 'Menü', 'key' => 'main', 'icon' => 'layout-grid', 'is_active' => 1])
        ->assertRedirectToRoute('admin.menus.index');
    $menu = Menu::query()->sole();
    expect($menu->icon)->toBe('layout-grid');
    $payload = ['label' => 'Hizmetler', 'target_type' => 'group', 'icon' => 'briefcase', 'sort_order' => 0, 'is_active' => 1];
    $this->post(route('admin.menus.items.store', $menu), $payload)->assertRedirectToRoute('admin.menus.index');
    $parent = $menu->items()->sole();
    $this->post(route('admin.menus.items.store', $menu), [...$payload, 'parent_id' => $parent->id, 'label' => 'Ekip', 'icon' => 'users'])
        ->assertRedirectToRoute('admin.menus.index');
    $child = $parent->children()->sole();

    $tree = app(NavigationService::class)->tree('main');
    expect($tree[0]['icon'])->toBe('briefcase');
    expect($tree[0]['children'][0]['icon'])->toBe('users');
    $this->get(route('admin.menus.items.edit', [$menu, $child]))
        ->assertRedirectToRoute('admin.menus.index', ['menu' => $menu->id, 'item' => $child->id]);
    $this->getJson(route('admin.menus.workspace', $menu))->assertOk()->assertJsonPath('items.1.icon', 'users');
    $this->put(route('admin.menus.items.update', [$menu, $child]), [...$payload, 'parent_id' => $parent->id, 'icon' => null])
        ->assertRedirectToRoute('admin.menus.index');
    expect($child->fresh()->icon)->toBeNull();
    $this->put(route('admin.menus.update', $menu), ['name' => 'Menü', 'key' => 'main', 'icon' => null, 'is_active' => 1])
        ->assertRedirectToRoute('admin.menus.index');
    expect($menu->fresh()->icon)->toBeNull();
});

test('menu icon fields reject markup without changing stored icons', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create(['icon' => 'users']);
    $item = $menu->items()->create(['label' => 'Grup', 'target_type' => 'group', 'icon' => 'briefcase', 'sort_order' => 0, 'is_active' => 1]);
    $this->actingAs($admin, 'admin')->put(route('admin.menus.update', $menu), ['name' => 'Menü', 'key' => $menu->key, 'icon' => '<svg onload="alert(1)">', 'is_active' => 1])
        ->assertSessionHasErrors('icon');
    $this->put(route('admin.menus.items.update', [$menu, $item]), ['label' => 'Grup', 'target_type' => 'group', 'icon' => '<script>', 'sort_order' => 0, 'is_active' => 1])
        ->assertSessionHasErrors('icon');
    expect($menu->fresh()->icon)->toBe('users');
    expect($item->fresh()->icon)->toBe('briefcase');
});

test('new standard settings can be saved and survive repeated seeding', function () {
    $this->seed(SettingsSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');
    $expected = ['contact.whatsapp' => '+90 555 111 22 33', 'contact.working_hours' => 'Hafta içi 09:00–18:00', 'social.linkedin' => 'https://www.linkedin.com/company/example'];
    $definitions = SettingDefinition::query()->whereIn('key', array_keys($expected))->get();
    expect($definitions)->toHaveCount(3);
    foreach ($definitions->groupBy('section_id') as $sectionId => $group) {
        $data = $group->mapWithKeys(fn (SettingDefinition $definition): array => [$definition->id => $expected[$definition->key]])->all();
        $this->put(route('admin.settings.update', $sectionId), ['settings' => $data])->assertSessionHasNoErrors()->assertRedirect();
    }
    $this->seed(SettingsSeeder::class);

    foreach ($expected as $key => $value) {
        expect(app(SettingsService::class)->get($key))->toBe($value);
    }
});
