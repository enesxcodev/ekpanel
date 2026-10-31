<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Services\NavigationService;
use Modules\Pages\Models\Page;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('menu workspace renders escaped menu choices and only supported content sources', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create(['name' => '<script>alert(1)</script>']);
    $this->actingAs($admin, 'admin')->get(route('admin.menus.index', ['menu' => $menu->id]))->assertOk()
        ->assertViewIs('menus::workspace')->assertSee('Menü öğesi ekle')
        ->assertSee(e(route('admin.menus.index', ['lang' => 'tr', 'menu' => $menu->id])), false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('Blog Yazıları')->assertDontSee('name="target_type" class="form-select"', false);
});

test('menu information and hidden tree items load through the selected menu endpoint', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create(['name' => 'Ana Menü']);
    $parent = MenuItem::factory()->for($menu)->group()->create();
    $child = MenuItem::factory()->for($menu)->hidden()->create(['parent_id' => $parent->id]);
    $other = MenuItem::factory()->create();
    $this->actingAs($admin, 'admin')->getJson(route('admin.menus.workspace', $menu))->assertOk()
        ->assertJsonPath('menu.name', 'Ana Menü')->assertJsonPath('menu.items_count', 2)
        ->assertJsonPath('items.1.id', $child->id)->assertJsonPath('items.1.is_active', false)
        ->assertJsonMissing(['id' => $other->id, 'label' => $other->label]);
});

test('menu and item JSON writes persist and return without a page redirect', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');
    $response = $this->postJson(route('admin.menus.store'), ['name' => 'Yeni Menü', 'key' => 'workspace-new', 'is_active' => true])
        ->assertCreated()->assertJsonPath('menu.key', 'workspace-new');
    $menu = Menu::query()->findOrFail($response->json('menu.id'));
    $itemResponse = $this->postJson(route('admin.menus.items.store', $menu), ['label' => 'İletişim', 'target_type' => 'url', 'url' => '/iletisim', 'is_active' => true, 'sort_order' => 0])
        ->assertCreated();
    $item = MenuItem::query()->findOrFail($itemResponse->json('id'));
    $this->putJson(route('admin.menus.items.update', [$menu, $item]), ['label' => 'Bize ulaşın', 'target_type' => 'url', 'url' => '/ulasin', 'is_active' => false, 'sort_order' => 2])->assertOk();
    $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'menu_id' => $menu->id, 'label' => 'Bize ulaşın', 'url' => '/ulasin', 'is_active' => false]);
    $this->putJson(route('admin.menus.update', $menu), ['name' => 'Güncel Menü', 'key' => 'workspace-new', 'is_active' => false])->assertOk();
    $this->assertDatabaseHas('menus', ['id' => $menu->id, 'name' => 'Güncel Menü', 'is_active' => false]);
    $this->deleteJson(route('admin.menus.items.destroy', [$menu, $item]))->assertOk();
    $this->assertModelMissing($item);
    $this->deleteJson(route('admin.menus.destroy', $menu))->assertOk();
    $this->assertModelMissing($menu);
});

test('reordering persists nesting and sibling positions in public navigation', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create(['key' => 'reordered', 'is_active' => true]);
    $group = MenuItem::factory()->for($menu)->group()->create(['label' => 'Kurumsal']);
    $first = MenuItem::factory()->for($menu)->create(['label' => 'Hakkımızda']);
    $second = MenuItem::factory()->for($menu)->create(['label' => 'İletişim']);
    $this->actingAs($admin, 'admin')->putJson(route('admin.menus.reorder', $menu), ['items' => [
        ['id' => $second->id, 'parent_id' => null, 'sort_order' => 0],
        ['id' => $group->id, 'parent_id' => null, 'sort_order' => 1],
        ['id' => $first->id, 'parent_id' => $group->id, 'sort_order' => 0],
    ]])->assertOk();
    $this->assertDatabaseHas('menu_items', ['id' => $first->id, 'parent_id' => $group->id, 'sort_order' => 0]);
    $tree = app(NavigationService::class)->tree($menu->key);
    expect($tree[0]['label'])->toBe('İletişim');
    expect($tree[1]['children'][0]['label'])->toBe('Hakkımızda');
});

test('typed item settings preserve module references and anchor fields', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create();
    $page = Page::factory()->create();
    $this->actingAs($admin, 'admin');
    $response = $this->postJson(route('admin.menus.items.store', $menu), [
        'label' => 'Sayfa', 'target_type' => 'content', 'source' => 'pages', 'source_id' => (string) $page->id, 'sort_order' => 0, 'is_active' => true,
    ])->assertCreated();
    $this->assertDatabaseHas('menu_items', ['id' => $response->json('id'), 'source' => 'pages', 'source_id' => (string) $page->id]);
    $item = MenuItem::query()->findOrFail($response->json('id'));
    $this->putJson(route('admin.menus.items.update', [$menu, $item]), [
        'label' => 'Sayfa güncellendi', 'target_type' => 'content', 'source' => 'pages', 'source_id' => (string) $page->id, 'sort_order' => 0, 'is_active' => true, 'new_tab' => true,
    ])->assertOk();
    $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'label' => 'Sayfa güncellendi', 'new_tab' => true]);
    $anchor = $this->postJson(route('admin.menus.items.store', $menu), [
        'label' => 'Bölüme git', 'target_type' => 'anchor', 'anchor' => 'iletisim', 'url' => '/sayfa', 'sort_order' => 1, 'is_active' => true,
    ])->assertCreated();
    $this->assertDatabaseHas('menu_items', ['id' => $anchor->json('id'), 'anchor' => 'iletisim', 'url' => '/sayfa']);
});

test('reordering rejects cycles foreign parents stale snapshots and duplicate ids without changing the tree', function (string $failure) {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create();
    $first = MenuItem::factory()->for($menu)->create(['sort_order' => 4]);
    $second = MenuItem::factory()->for($menu)->create(['sort_order' => 8]);
    $foreign = MenuItem::factory()->create();
    $positions = [
        ['id' => $first->id, 'parent_id' => null, 'sort_order' => 0],
        ['id' => $second->id, 'parent_id' => null, 'sort_order' => 1],
    ];
    if ($failure === 'cycle') {
        $positions[0]['parent_id'] = $second->id;
        $positions[1]['parent_id'] = $first->id;
    } elseif ($failure === 'foreign-parent') {
        $positions[1]['parent_id'] = $foreign->id;
    } elseif ($failure === 'missing-item') {
        array_pop($positions);
    } elseif ($failure === 'duplicate') {
        $positions[1]['id'] = $first->id;
    } else {
        $positions[1]['id'] = $foreign->id;
    }
    $this->actingAs($admin, 'admin')->putJson(route('admin.menus.reorder', $menu), ['items' => $positions])->assertUnprocessable();
    $this->assertDatabaseHas('menu_items', ['id' => $first->id, 'parent_id' => null, 'sort_order' => 4]);
    $this->assertDatabaseHas('menu_items', ['id' => $second->id, 'parent_id' => null, 'sort_order' => 8]);
})->with(['cycle', 'foreign-parent', 'missing-item', 'duplicate', 'foreign-item']);

test('automatic children cannot receive manual items and parents cannot be deleted', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create();
    $automatic = MenuItem::factory()->for($menu)->create(['dynamic_children' => true]);
    $manual = MenuItem::factory()->for($menu)->create();
    $this->actingAs($admin, 'admin')->putJson(route('admin.menus.reorder', $menu), ['items' => [
        ['id' => $automatic->id, 'parent_id' => null, 'sort_order' => 0],
        ['id' => $manual->id, 'parent_id' => $automatic->id, 'sort_order' => 0],
    ]])->assertUnprocessable()->assertJsonValidationErrors('items');
    $manual->update(['parent_id' => $automatic->id]);
    $this->deleteJson(route('admin.menus.items.destroy', [$menu, $automatic]))->assertUnprocessable();
    $this->assertModelExists($automatic);
    $this->assertModelExists($manual);
});

test('item writes reject unsafe targets and cross menu item bindings', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $menu = Menu::factory()->create();
    $foreign = MenuItem::factory()->create();
    $data = ['label' => 'Tehlikeli', 'target_type' => 'url', 'url' => 'javascript:alert(1)', 'is_active' => true, 'sort_order' => 0];
    $this->actingAs($admin, 'admin')->postJson(route('admin.menus.items.store', $menu), $data)->assertUnprocessable()->assertJsonValidationErrors('url');
    $this->assertDatabaseMissing('menu_items', ['menu_id' => $menu->id]);
    $data['url'] = '/safe';
    $this->putJson(route('admin.menus.items.update', [$menu, $foreign]), $data)->assertNotFound();
    expect($foreign->fresh()->label)->not->toBe('Tehlikeli');
});

test('view permission does not grant write operations and guests cannot read menu data', function () {
    $menu = Menu::factory()->create();
    $this->getJson(route('admin.menus.workspace', $menu))->assertUnauthorized();
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('menus.view');
    $this->actingAs($admin, 'admin')->getJson(route('admin.menus.workspace', $menu))->assertOk();
    $this->putJson(route('admin.menus.reorder', $menu), ['items' => []])->assertForbidden();
    $this->postJson(route('admin.menus.items.store', $menu), ['label' => 'Denied'])->assertForbidden();
    $this->putJson(route('admin.menus.update', $menu), ['name' => 'Denied'])->assertForbidden();
    $this->assertDatabaseMissing('menu_items', ['menu_id' => $menu->id]);
    expect($menu->fresh()->name)->not->toBe('Denied');
});

test('legacy form addresses enter the selected menu workspace', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $item = MenuItem::factory()->create();
    $this->actingAs($admin, 'admin')->get(route('admin.menus.items.edit', [$item->menu, $item]))
        ->assertRedirectToRoute('admin.menus.index', ['menu' => $item->menu_id, 'item' => $item->id, 'lang' => 'tr']);
    $this->get(route('admin.menus.edit', $item->menu))->assertRedirectToRoute('admin.menus.index', ['menu' => $item->menu_id, 'lang' => 'tr']);
});

test('translated menu trees remain language scoped during CRUD, reorder, and public rendering', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $english = Language::factory()->create(['code' => 'en', 'is_active' => true]);
    $menu = Menu::factory()->create(['key' => 'language-scope']);
    $menu->translations()->create(['lang_id' => $english->id, 'name' => 'Main navigation']);
    $turkishItem = MenuItem::factory()->for($menu)->create(['label' => 'Türkçe', 'url' => '/tr-link']);
    $englishItem = MenuItem::factory()->for($menu)->create(['lang_id' => $english->id, 'label' => 'English', 'url' => '/en-link']);
    $this->actingAs($admin, 'admin');

    $this->getJson(route('admin.menus.workspace', ['menu' => $menu->id, 'lang' => 'en']))
        ->assertOk()->assertJsonPath('menu.name', 'Main navigation')->assertJsonPath('items.0.id', $englishItem->id)
        ->assertJsonCount(1, 'items')->assertJsonMissing(['label' => 'Türkçe']);
    $this->putJson(route('admin.menus.reorder', ['menu' => $menu->id, 'lang' => 'en']), [
        'lang_id' => $english->id,
        'items' => [['id' => $englishItem->id, 'parent_id' => null, 'sort_order' => 4]],
    ])->assertOk();
    $this->deleteJson(route('admin.menus.items.destroy', ['menu' => $menu->id, 'item' => $englishItem->id, 'lang' => 'en']), ['lang_id' => $english->id])->assertOk();

    expect($turkishItem->fresh())->not->toBeNull();
    expect(app(NavigationService::class)->tree($menu->key, 'en'))->toBeEmpty();
    expect(app(NavigationService::class)->tree($menu->key, 'tr')[0]['label'])->toBe('Türkçe');
});
