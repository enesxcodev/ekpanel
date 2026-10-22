<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Projects\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Projects\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Projects\Dashboard\Widgets\RecordsCountWidget;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;

uses(LazilyRefreshDatabase::class);

test('projects.total widget reports the domain count and excludes ineligible records', function () {
    Project::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('projects.active widget reports the domain count and excludes ineligible records', function () {
    Project::factory()->create(['is_active' => true]);
    Project::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});

test('projects.categories.count widget reports the domain count and excludes ineligible records', function () {
    ProjectCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
