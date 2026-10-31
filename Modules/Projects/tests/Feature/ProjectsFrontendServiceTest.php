<?php

use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Models\ProjectCategoryTranslation;
use Modules\Projects\Models\ProjectTranslation;
use Modules\Projects\Services\ProjectsFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('projects-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Projects' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = Project::factory()->count(3)->create(['is_active' => true]);
    Project::factory()->create(['is_active' => false]);

    $result = app(ProjectsFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = Project::factory()->create(['is_active' => false]);
    $record = Project::factory()->create(['is_active' => true]);
    $service = app(ProjectsFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(ProjectsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = Project::factory()->create(['is_active' => true]);
    Storage::disk('projects-frontend-statuses')->put('statuses.json', json_encode(['Projects' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(ProjectsFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('projects', 'unavailable_projects');
    $service = app(ProjectsFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_projects', 'projects');
    }
});

test('category filtering excludes unrelated content', function () {
    $category = ProjectCategory::factory()->create();
    $record = Project::factory()->for($category, 'category')->create(['is_active' => true]);
    Project::factory()->create(['is_active' => true]);
    $service = app(ProjectsFrontendService::class);

    expect($service->getAll($category->id)->modelKeys())->toEqualCanonicalizing([$record->id]);
    expect($service->getFirst($category->id)?->id)->toBe($record->id);
    expect($service->getAll(999))->toBeEmpty();
});

test('uses the configured content order', function () {
    $later = Project::factory()->create([...['is_active' => true], 'sort_order' => 20]);
    $earlier = Project::factory()->create([...['is_active' => true], 'sort_order' => 10]);

    $result = app(ProjectsFrontendService::class)->getAll();

    expect($result->modelKeys())->toBe([$earlier->id, $later->id]);
});

test('returns only translated published projects with a category translation in the requested language', function () {
    $english = Language::factory()->create(['code' => 'en', 'is_active' => true]);
    $translatedCategory = ProjectCategory::factory()->create();
    ProjectCategoryTranslation::query()->create(['project_category_id' => $translatedCategory->id, 'lang_id' => $english->id, 'name' => 'Completed work', 'slug' => 'completed-work']);
    $translatedProject = Project::factory()->for($translatedCategory, 'category')->create(['is_active' => true]);
    ProjectTranslation::query()->create(['project_id' => $translatedProject->id, 'lang_id' => $english->id, 'title' => 'English project', 'slug' => 'english-project', 'content' => '<p>English copy</p>']);
    $missingCategoryTranslation = Project::factory()->create(['is_active' => true]);
    ProjectTranslation::query()->create(['project_id' => $missingCategoryTranslation->id, 'lang_id' => $english->id, 'title' => 'Should stay hidden', 'slug' => 'hidden-project']);
    Project::factory()->create(['is_active' => true]);

    $result = app(ProjectsFrontendService::class)->getAll(null, 'en');

    expect($result->modelKeys())->toBe([$translatedProject->id])
        ->and($result->first()->selectedTranslation->title)->toBe('English project')
        ->and($result->first()->category->selectedTranslation->name)->toBe('Completed work');
});

test('returns empty results for an inactive frontend language', function () {
    Language::factory()->inactive()->create(['code' => 'en']);
    Project::factory()->create(['is_active' => true]);

    expect(app(ProjectsFrontendService::class)->getAll(null, 'en'))->toBeEmpty();
});
