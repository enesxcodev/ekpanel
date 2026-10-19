<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Services\BlogFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('blog-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Blog' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = BlogContent::factory()->count(3)->create(['status' => 'published']);
    BlogContent::factory()->create(['status' => 'draft']);

    $result = app(BlogFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = BlogContent::factory()->create(['status' => 'draft']);
    $record = BlogContent::factory()->create(['status' => 'published']);
    $service = app(BlogFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(BlogFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = BlogContent::factory()->create(['status' => 'published']);
    Storage::disk('blog-frontend-statuses')->put('statuses.json', json_encode(['Blog' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(BlogFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('blog_contents', 'unavailable_blog_contents');
    $service = app(BlogFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_blog_contents', 'blog_contents');
    }
});

test('category filtering includes descendants and excludes unrelated content', function () {
    $category = BlogCategory::factory()->create();
    $record = BlogContent::factory()->for($category, 'category')->create(['status' => 'published']);
    $child = BlogCategory::factory()->create(['parent_id' => $category->id]);
    $childRecord = BlogContent::factory()->for($child, 'category')->create(['status' => 'published']);
    BlogContent::factory()->create(['status' => 'published']);
    $service = app(BlogFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id, $childRecord->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});
