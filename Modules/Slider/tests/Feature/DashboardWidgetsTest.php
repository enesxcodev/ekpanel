<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Slider\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Slider\Dashboard\Widgets\RecordsCountWidget;
use Modules\Slider\Models\Slider;

uses(LazilyRefreshDatabase::class);

test('slider.total widget reports the domain count and excludes ineligible records', function () {
    Slider::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('slider.active widget reports the domain count and excludes ineligible records', function () {
    Slider::factory()->create(['is_active' => true]);
    Slider::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
