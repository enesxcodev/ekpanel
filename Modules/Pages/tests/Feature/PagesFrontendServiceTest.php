<?php

use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Services\PagesFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('pages-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Pages' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Page::factory()->count(3)->create(['status' => 'published']);
    Page::factory()->create(['status' => 'draft']);

    $result = app(PagesFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Page::factory()->create(['status' => 'draft']);
    $record = Page::factory()->create(['status' => 'published']);
    $service = app(PagesFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(PagesFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Page::factory()->create(['status' => 'published']);
    Storage::disk('pages-frontend-statuses')->put('statuses.json', json_encode(['Pages' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(PagesFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('pages', 'unavailable_pages');
    $service = app(PagesFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_pages', 'pages');
    }
});

test('category filtering includes descendants and excludes unrelated content', function () {
    $category = PageCategory::factory()->create();
    $record = Page::factory()->for($category, 'category')->create(['status' => 'published', 'sort_order' => 1]);
    $child = PageCategory::factory()->create(['parent_id' => $category->id]);
    $childRecord = Page::factory()->for($child, 'category')->create(['status' => 'published', 'sort_order' => 2]);
    Page::factory()->create(['status' => 'published']);
    $service = app(PagesFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id, $childRecord->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});

test('uses the configured content order', function () {
    $later = Page::factory()->create([...['status' => 'published'], 'sort_order' => 20]);
    $earlier = Page::factory()->create([...['status' => 'published'], 'sort_order' => 10]);

    $result = app(PagesFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});

test('returns translated page and category fields for the selected active language', function () {
    $english = Language::factory()->create(['code' => 'en', 'is_active' => true]);
    $category = PageCategory::factory()->create(['name' => 'Hakkımızda', 'slug' => 'hakkimizda']);
    $category->translations()->create([
        'lang_id' => $english->id,
        'scope_id' => 0,
        'name' => 'About us',
        'slug' => 'about-us',
    ]);
    $page = Page::factory()->for($category, 'category')->create(['title' => 'Biz', 'slug' => 'biz']);
    $page->translations()->create([
        'lang_id' => $english->id,
        'scope_id' => $category->id,
        'title' => 'Our company',
        'slug' => 'our-company',
    ]);

    $translated = app(PagesFrontendService::class)->find($page->id, 'en');

    expect($translated?->title)->toBe('Our company')
        ->and($translated?->slug)->toBe('our-company')
        ->and($translated?->category?->name)->toBe('About us')
        ->and($translated?->category?->slug)->toBe('about-us');
});
