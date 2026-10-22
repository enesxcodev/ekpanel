<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Services\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Services\Dashboard\Widgets\RecordsCountWidget;
use Modules\Services\Dashboard\Widgets\ServiceCountWidget;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;

uses(LazilyRefreshDatabase::class);

test('services.total widget reports the domain count and excludes ineligible records', function () {
    Service::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('services.categories.count widget reports the domain count and excludes ineligible records', function () {
    ServiceCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('services.active widget reports the domain count and excludes ineligible records', function () {
    Service::factory()->create(['is_active' => true]);
    Service::factory()->create(['is_active' => false]);

    $result = (new ServiceCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
