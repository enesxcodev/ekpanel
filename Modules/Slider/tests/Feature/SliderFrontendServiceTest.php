<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Slider\Models\Slider;
use Modules\Slider\Services\SliderFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('slider-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Slider' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('single banner excludes inactive records and includes its media relation', function () {
    Slider::factory()->create(['is_active' => false]);
    $active = Slider::factory()->create(['is_active' => true]);

    $banner = app(SliderFrontendService::class)->getFirst();

    expect($banner?->id)->toBe($active->id);
    expect($banner->relationLoaded('imageMedia'))->toBeTrue();
});

test('carousel includes all active banners and excludes inactive records', function () {
    $active = Slider::factory()->count(3)->create(['is_active' => true]);
    Slider::factory()->create(['is_active' => false]);

    $banners = app(SliderFrontendService::class)->getAll();

    expect($banners->modelKeys())->toEqualCanonicalizing($active->modelKeys());
});

test('returns empty results when no active banners exist', function () {
    Slider::factory()->create(['is_active' => false]);
    $service = app(SliderFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module returns empty results despite existing active banners', function () {
    Slider::factory()->create(['is_active' => true]);
    Storage::disk('slider-frontend-statuses')->put('statuses.json', json_encode(['Slider' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(SliderFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('unavailable slider table returns empty results', function () {
    Schema::rename('sliders', 'unavailable_sliders');
    $service = app(SliderFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_sliders', 'sliders');
    }
});
