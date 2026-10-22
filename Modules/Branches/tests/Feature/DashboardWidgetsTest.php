<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Branches\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Branches\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Branches\Dashboard\Widgets\RecordsCountWidget;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;

uses(LazilyRefreshDatabase::class);

test('branches.total widget reports the domain count and excludes ineligible records', function () {
    Branch::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('branches.active widget reports the domain count and excludes ineligible records', function () {
    Branch::factory()->create(['is_active' => true]);
    Branch::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});

test('branches.categories.count widget reports the domain count and excludes ineligible records', function () {
    BranchCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
