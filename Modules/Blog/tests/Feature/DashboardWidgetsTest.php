<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Blog\Dashboard\Widgets\CategoriesCountWidget;
use Modules\Blog\Dashboard\Widgets\PostsCountWidget;
use Modules\Blog\Dashboard\Widgets\TagsCountWidget;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Models\BlogTag;

uses(LazilyRefreshDatabase::class);

test('blog.posts.count widget reports the domain count and excludes ineligible records', function () {
    BlogContent::factory()->count(2)->create();

    $result = (new PostsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('blog.categories.count widget reports the domain count and excludes ineligible records', function () {
    BlogCategory::factory()->count(2)->create();

    $result = (new CategoriesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('blog.tags.count widget reports the domain count and excludes ineligible records', function () {
    BlogTag::factory()->count(2)->create();

    $result = (new TagsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
