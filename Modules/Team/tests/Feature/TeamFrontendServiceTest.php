<?php

use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Team\Models\TeamMember;
use Modules\Team\Services\TeamFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $disk = Storage::fake('team-frontend-statuses');
    $disk->put('statuses.json', json_encode(['Team' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('lists only published frontend records', function () {
    $records = TeamMember::factory()->count(3)->create(['is_active' => true]);
    TeamMember::factory()->create(['is_active' => false]);

    $result = app(TeamFrontendService::class)->getAll();

    expect($result->modelKeys())->toEqualCanonicalizing($records->modelKeys());
});

test('returns only members translated into the requested active language', function () {
    $english = Language::query()->firstOrCreate(['code' => 'en'], ['is_active' => true]);
    $english->forceFill(['is_active' => true])->save();
    $translated = TeamMember::factory()->create(['is_active' => true]);
    $translated->translations()->create(['lang_id' => $english->id, 'name' => 'English member', 'title' => 'English title']);
    $untranslated = TeamMember::factory()->create(['is_active' => true]);

    $results = app(TeamFrontendService::class)->getAll('en');

    expect($results->modelKeys())->toBe([$translated->id]);
    expect($results->first()?->name)->toBe('English member');
    expect(app(TeamFrontendService::class)->find($untranslated->id, 'en'))->toBeNull();
});

test('single record and identifier lookup respect frontend visibility', function () {
    $hidden = TeamMember::factory()->create(['is_active' => false]);
    $record = TeamMember::factory()->create(['is_active' => true]);
    $service = app(TeamFrontendService::class);

    expect($service->getFirst()?->id)->toBe($record->id);
    expect($service->find($record->id)?->id)->toBe($record->id);
    expect($service->find($hidden->id))->toBeNull();
});

test('returns empty results for missing content', function () {
    $service = app(TeamFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find(999))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('disabled module does not expose existing content', function () {
    $record = TeamMember::factory()->create(['is_active' => true]);
    Storage::disk('team-frontend-statuses')->put('statuses.json', json_encode(['Team' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(TeamFrontendService::class);

    expect($service->getFirst())->toBeNull();
    expect($service->find($record->id))->toBeNull();
    expect($service->getAll())->toBeEmpty();
});

test('missing module table does not raise a query error', function () {
    Schema::rename('team_members', 'unavailable_team_members');
    $service = app(TeamFrontendService::class);

    try {
        expect($service->getFirst())->toBeNull();
        expect($service->find(999))->toBeNull();
        expect($service->getAll())->toBeEmpty();
    } finally {
        Schema::rename('unavailable_team_members', 'team_members');
    }
});
