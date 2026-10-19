<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Services\ServicesFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('services-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Services' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Service::factory()->count(3)->create(['is_active' => true]);
    Service::factory()->create(['is_active' => false]);

    $result = app(ServicesFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Service::factory()->create(['is_active' => false]);
    $record = Service::factory()->create(['is_active' => true]);
    $service = app(ServicesFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(ServicesFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Service::factory()->create(['is_active' => true]);
    Storage::disk('services-frontend-statuses')->put('statuses.json', json_encode(['Services' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(ServicesFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('services', 'unavailable_services');
    $service = app(ServicesFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_services', 'services');
    }
});

test('category filtering excludes unrelated content', function () {
    $category = ServiceCategory::factory()->create();
    $record = Service::factory()->for($category, 'category')->create(['is_active' => true]);
    Service::factory()->create(['is_active' => true]);
    $service = app(ServicesFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});

test('uses the configured content order', function () {
    $later = Service::factory()->create([...['is_active' => true], 'sort_order' => 20]);
    $earlier = Service::factory()->create([...['is_active' => true], 'sort_order' => 10]);

    $result = app(ServicesFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});

test('inactive categories hide their services while uncategorized services remain visible', function () {
    $hiddenCategory = ServiceCategory::factory()->create(['is_active' => false]);
    $hidden = Service::factory()->for($hiddenCategory, 'category')->create();
    $uncategorized = Service::factory()->create();
    $service = app(ServicesFrontendService::class);

    expect($service->getAll()->modelKeys())->toBe([$uncategorized->id]);
    expect($service->find($hidden->id))->toBeNull();
});
