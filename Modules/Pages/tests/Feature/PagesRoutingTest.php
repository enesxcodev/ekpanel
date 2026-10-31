<?php

use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('explicit routes registered after pages take precedence over matching published pages', function () {
    Page::factory()->create(['page_category_id' => null, 'slug' => 'routing-check']);
    Route::get('/routing-check', fn (): string => 'Explicit route response');

    $this->get('/routing-check')->assertOk()->assertContent('Explicit route response');
});

test('published pages remain accessible through their generated URLs', function (bool $categorized) {
    $category = $categorized ? PageCategory::factory()->create(['slug' => 'kurumsal']) : null;
    $page = Page::factory()->create(['page_category_id' => $category?->id, 'slug' => 'hakkimizda']);

    $this->get(route('pages.show', ['path' => $categorized ? 'kurumsal/hakkimizda' : 'hakkimizda']))
        ->assertOk()->assertViewIs('pages::pages.show')->assertViewHas('page', fn (Page $rendered): bool => $rendered->is($page));
})->with(['root page' => false, 'categorized page' => true]);

test('draft pages are not accessible through the public page route', function () {
    Page::factory()->create(['page_category_id' => null, 'slug' => 'draft-page', 'status' => 'draft']);

    $this->get('/draft-page')->assertNotFound();
});

test('unknown page paths return 404', function () {
    $this->get('/missing-category/missing-page')->assertNotFound();
});

test('published pages resolve through localized category and page slugs', function () {
    $english = Language::factory()->create(['code' => 'en', 'is_active' => true]);
    $category = PageCategory::factory()->create(['slug' => 'kurumsal']);
    $category->translations()->create(['lang_id' => $english->id, 'scope_id' => 0, 'name' => 'Company', 'slug' => 'company']);
    $page = Page::factory()->for($category, 'category')->create(['slug' => 'hakkimizda']);
    $page->translations()->create(['lang_id' => $english->id, 'scope_id' => $category->id, 'title' => 'About us', 'slug' => 'about-us']);
    expect($category->translations()->where('lang_id', $english->id)->exists())->toBeTrue()
        ->and($page->translations()->where('lang_id', $english->id)->exists())->toBeTrue();
    expect(PageCategory::query()->whereNull('parent_id')->whereHas('translations', fn ($query) => $query->where('lang_id', $english->id)->where('slug', 'company'))->exists())->toBeTrue()
        ->and(Page::query()->whereHas('translations', fn ($query) => $query->where('lang_id', $english->id)->where('slug', 'about-us'))->exists())->toBeTrue();

    $response = $this->get('/en/company/about-us');
    expect(request()->route()?->getName())->toBe('pages.localized.show');

    $response->assertOk()
        ->assertViewIs('pages::pages.show')
        ->assertViewHas('page', fn (Page $rendered): bool => $rendered->is($page) && $rendered->title === 'About us');
});
