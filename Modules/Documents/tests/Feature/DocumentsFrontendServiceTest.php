<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;
use Modules\Documents\Services\DocumentsFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('documents-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Documents' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Document::factory()->count(3)->create(['status' => 'published']);
    Document::factory()->create(['status' => 'draft']);

    $result = app(DocumentsFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Document::factory()->create(['status' => 'draft']);
    $record = Document::factory()->create(['status' => 'published']);
    $service = app(DocumentsFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(DocumentsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Document::factory()->create(['status' => 'published']);
    Storage::disk('documents-frontend-statuses')->put('statuses.json', json_encode(['Documents' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(DocumentsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('documents', 'unavailable_documents');
    $service = app(DocumentsFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_documents', 'documents');
    }
});

test('category filtering excludes unrelated content', function () {
    $category = DocumentCategory::factory()->create();
    $record = Document::factory()->for($category, 'category')->create(['status' => 'published']);
    Document::factory()->create(['status' => 'published']);
    $service = app(DocumentsFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});
