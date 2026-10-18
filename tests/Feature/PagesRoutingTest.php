<?php

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
