<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Faqs\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Faqs\Dashboard\Widgets\RecordsCountWidget;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;

uses(LazilyRefreshDatabase::class);

test('faqs.total widget reports the domain count and excludes ineligible records', function () {
    Faq::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('faqs.categories.count widget reports the domain count and excludes ineligible records', function () {
    FaqCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
