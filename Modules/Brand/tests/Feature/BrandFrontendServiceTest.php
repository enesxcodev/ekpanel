<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Models\Brand;
use Modules\Brand\Services\BrandFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('brand-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Brand' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Brand::factory()->count(3)->create(['is_active' => true]);
    Brand::factory()->create(['is_active' => false]);

    $result = app(BrandFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Brand::factory()->create(['is_active' => false]);
    $record = Brand::factory()->create(['is_active' => true]);
    $service = app(BrandFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(BrandFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Brand::factory()->create(['is_active' => true]);
    Storage::disk('brand-frontend-statuses')->put('statuses.json', json_encode(['Brand' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(BrandFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('brands', 'unavailable_brands');
    $service = app(BrandFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_brands', 'brands');
    }
});

test('uses the configured content order', function () {
    $later = Brand::factory()->create([...['is_active' => true], 'sort_order' => 20]);
    $earlier = Brand::factory()->create([...['is_active' => true], 'sort_order' => 10]);

    $result = app(BrandFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});
