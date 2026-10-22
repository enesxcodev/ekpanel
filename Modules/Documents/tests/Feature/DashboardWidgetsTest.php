<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Documents\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Documents\Dashboard\Widgets\DocumentsCountWidget;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;

uses(LazilyRefreshDatabase::class);

test('documents.total widget reports the domain count and excludes ineligible records', function () {
    Document::factory()->count(2)->create();

    $result = (new DocumentsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('documents.categories.count widget reports the domain count and excludes ineligible records', function () {
    DocumentCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
