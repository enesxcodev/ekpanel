<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Popup\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Popup\Dashboard\Widgets\RecordsCountWidget;
use Modules\Popup\Models\Popup;

uses(LazilyRefreshDatabase::class);

test('popup.total widget reports the domain count and excludes ineligible records', function () {
    Popup::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('popup.active widget reports the domain count and excludes ineligible records', function () {
    Popup::factory()->create(['is_active' => true]);
    Popup::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
