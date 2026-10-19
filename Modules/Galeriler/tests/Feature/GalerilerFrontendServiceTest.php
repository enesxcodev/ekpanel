<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;
use Modules\Galeriler\Services\GalerilerFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('galeriler-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Galeriler' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists all frontend records', function () {
    $records = GalerilerItem::factory()->count(3)->create([]);

    $result = app(GalerilerFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $record = GalerilerItem::factory()->create([]);
    $service = app(GalerilerFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
});

test('returns empty results for missing content', function () {
    $service = app(GalerilerFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = GalerilerItem::factory()->create([]);
    Storage::disk('galeriler-frontend-statuses')->put('statuses.json', json_encode(['Galeriler' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(GalerilerFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('galeriler_items', 'unavailable_galeriler_items');
    $service = app(GalerilerFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_galeriler_items', 'galeriler_items');
    }
});

test('category filtering includes descendants and excludes unrelated content', function () {
    $category = GalerilerCategory::factory()->create();
    $record = GalerilerItem::factory()->for($category, 'category')->create(['sort_order' => 1]);
    $child = GalerilerCategory::factory()->create(['parent_id' => $category->id]);
    $childRecord = GalerilerItem::factory()->for($child, 'category')->create(['sort_order' => 2]);
    GalerilerItem::factory()->create([]);
    $service = app(GalerilerFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id, $childRecord->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});

test('uses the configured content order', function () {
    $later = GalerilerItem::factory()->create([...[], 'sort_order' => 20]);
    $earlier = GalerilerItem::factory()->create([...[], 'sort_order' => 10]);

    $result = app(GalerilerFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});
