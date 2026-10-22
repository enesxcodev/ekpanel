<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Brand\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Brand\Dashboard\Widgets\RecordsCountWidget;
use Modules\Brand\Models\Brand;

uses(LazilyRefreshDatabase::class);

test('brand.total widget reports the domain count and excludes ineligible records', function () {
    Brand::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('brand.active widget reports the domain count and excludes ineligible records', function () {
    Brand::factory()->create(['is_active' => true]);
    Brand::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
