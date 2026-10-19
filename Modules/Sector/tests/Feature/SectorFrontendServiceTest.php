<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Sector\Models\Sector;
use Modules\Sector\Services\SectorFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('sector-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Sector' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Sector::factory()->count(3)->create(['is_active' => true]);
    Sector::factory()->create(['is_active' => false]);

    $result = app(SectorFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Sector::factory()->create(['is_active' => false]);
    $record = Sector::factory()->create(['is_active' => true]);
    $service = app(SectorFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(SectorFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Sector::factory()->create(['is_active' => true]);
    Storage::disk('sector-frontend-statuses')->put('statuses.json', json_encode(['Sector' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(SectorFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('sectors', 'unavailable_sectors');
    $service = app(SectorFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_sectors', 'sectors');
    }
});

test('uses the configured content order', function () {
    $later = Sector::factory()->create([...['is_active' => true], 'sort_order' => 20]);
    $earlier = Sector::factory()->create([...['is_active' => true], 'sort_order' => 10]);

    $result = app(SectorFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});
