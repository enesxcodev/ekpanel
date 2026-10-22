<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Pages\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Pages\Dashboard\Widgets\PublishedPagesWidget;
use Modules\Pages\Dashboard\Widgets\RecordsCountWidget;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

uses(LazilyRefreshDatabase::class);

test('pages.total widget reports the domain count and excludes ineligible records', function () {
    Page::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('pages.published widget reports the domain count and excludes ineligible records', function () {
    Page::factory()->create(['status' => 'published']);
    Page::factory()->create(['status' => 'draft']);

    $result = (new PublishedPagesWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});

test('pages.categories.count widget reports the domain count and excludes ineligible records', function () {
    PageCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
