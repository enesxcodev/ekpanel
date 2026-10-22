<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Menus\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Menus\Dashboard\Widgets\RecordsCountWidget;
use Modules\Menus\Models\Menu;

uses(LazilyRefreshDatabase::class);

test('menus.total widget reports the domain count and excludes ineligible records', function () {
    Menu::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('menus.active widget reports the domain count and excludes ineligible records', function () {
    Menu::factory()->create(['is_active' => true]);
    Menu::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
