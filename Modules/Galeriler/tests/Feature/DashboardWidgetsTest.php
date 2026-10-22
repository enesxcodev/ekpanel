<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Galeriler\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Galeriler\Dashboard\Widgets\RecordsCountWidget;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;

uses(LazilyRefreshDatabase::class);

test('galeriler.total widget reports the domain count and excludes ineligible records', function () {
    GalerilerItem::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('galeriler.categories.count widget reports the domain count and excludes ineligible records', function () {
    GalerilerCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
