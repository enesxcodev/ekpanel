<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Services\MenusFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('menus-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Menus' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('returns the resolved menu tree including nested children', function () {
    $menu = Menu::factory()->create();
    $parent = MenuItem::factory()->for($menu)->group()->create(['label' => 'Kurumsal']);
    MenuItem::factory()->for($menu)->create(['parent_id' => $parent->id, 'label' => 'İletişim', 'url' => '/iletisim']);
    MenuItem::factory()->for($menu)->hidden()->create();

    $tree = app(MenusFrontendService::class)->getTree($menu->key);

    expect($tree)->toHaveCount(1);
    expect($tree[0]['label'])->toBe('Kurumsal');
    expect($tree[0]['children'][0]['label'])->toBe('İletişim');
    expect($tree[0]['children'][0]['url'])->toBe('/iletisim');
});

test('returns an empty tree for missing or inactive menus', function () {
    $menu = Menu::factory()->hidden()->create();
    $service = app(MenusFrontendService::class);

    expect($service->getTree($menu->key))->toBeEmpty();
    expect($service->getTree('missing-menu'))->toBeEmpty();
});

test('disabled menus module does not expose an existing menu', function () {
    $menu = Menu::factory()->create();
    MenuItem::factory()->for($menu)->create();
    Storage::disk('menus-frontend-statuses')->put('statuses.json', json_encode(['Menus' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);

    expect(app(MenusFrontendService::class)->getTree($menu->key))->toBeEmpty();
});

test('missing menu items table returns an empty tree', function () {
    Schema::rename('menu_items', 'unavailable_menu_items');

    try {
        expect(app(MenusFrontendService::class)->getTree('main'))->toBeEmpty();
    } finally {
        Schema::rename('unavailable_menu_items', 'menu_items');
    }
});
