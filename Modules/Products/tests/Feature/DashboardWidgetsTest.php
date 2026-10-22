<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Products\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Products\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Products\Dashboard\Widgets\RecordsCountWidget;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;

uses(LazilyRefreshDatabase::class);

test('products.total widget reports the domain count and excludes ineligible records', function () {
    Product::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('products.active widget reports the domain count and excludes ineligible records', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});

test('products.categories.count widget reports the domain count and excludes ineligible records', function () {
    ProductCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
