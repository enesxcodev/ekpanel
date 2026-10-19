<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;
use Modules\Faqs\Services\FaqsFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('faqs-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Faqs' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists all frontend records', function () {
    $records = Faq::factory()->count(3)->create([]);

    $result = app(FaqsFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $record = Faq::factory()->create([]);
    $service = app(FaqsFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
});

test('returns empty results for missing content', function () {
    $service = app(FaqsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Faq::factory()->create([]);
    Storage::disk('faqs-frontend-statuses')->put('statuses.json', json_encode(['Faqs' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(FaqsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('faqs', 'unavailable_faqs');
    $service = app(FaqsFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_faqs', 'faqs');
    }
});

test('category filtering excludes unrelated content', function () {
    $category = FaqCategory::factory()->create();
    $record = Faq::factory()->for($category, 'category')->create([]);
    Faq::factory()->create([]);
    $service = app(FaqsFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});
