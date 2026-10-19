<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Testimonials\Models\Testimonial;
use Modules\Testimonials\Services\TestimonialsFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('testimonials-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Testimonials' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Testimonial::factory()->count(3)->create(['is_active' => true]);
    Testimonial::factory()->create(['is_active' => false]);

    $result = app(TestimonialsFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Testimonial::factory()->create(['is_active' => false]);
    $record = Testimonial::factory()->create(['is_active' => true]);
    $service = app(TestimonialsFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(TestimonialsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Testimonial::factory()->create(['is_active' => true]);
    Storage::disk('testimonials-frontend-statuses')->put('statuses.json', json_encode(['Testimonials' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(TestimonialsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('testimonials', 'unavailable_testimonials');
    $service = app(TestimonialsFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_testimonials', 'testimonials');
    }
});

test('uses the configured content order', function () {
    $later = Testimonial::factory()->create([...['is_active' => true], 'sort_order' => 20]);
    $earlier = Testimonial::factory()->create([...['is_active' => true], 'sort_order' => 10]);

    $result = app(TestimonialsFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});
