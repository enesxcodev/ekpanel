<?php

use App\Models\Settings\Language;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Popup\Models\Popup;
use Modules\Popup\Services\PopupFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

test('published popups respect active status boundaries and deterministic overlap order', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00', 'UTC'));
    $first = Popup::factory()->create(['starts_at' => now()]);
    $second = Popup::factory()->create(['starts_at' => now()]);
    $older = Popup::factory()->create(['starts_at' => now()->subHour()]);
    Popup::factory()->create(['is_active' => false]);
    Popup::factory()->create(['starts_at' => now()->addSecond()]);
    Popup::factory()->create(['ends_at' => now()]);
    $service = app(PopupFrontendService::class);

    expect($service->published()->modelKeys())->toBe([$second->id, $first->id, $older->id]);
    expect($service->published()->first()->relationLoaded('imageMedia'))->toBeTrue();
    expect($service->eligible([$second->id => ['closed_at' => now()]])->modelKeys())->toBe([$first->id, $older->id]);
    expect($service->shouldShow($first, at: now()))->toBeTrue();
    expect($service->shouldShow($first, closedAt: now()))->toBeFalse();
    expect($service->shouldShow($first, lastShownAt: now()))->toBeTrue();
    expect($service->shouldShow($first, at: $first->ends_at))->toBeFalse();
});

test('interval policy waits from the later visitor event and includes the exact expiry', function (string $unit, int $interval, string $shown, ?string $closed, string $at, bool $expected) {
    $popup = Popup::factory()->create(['starts_at' => '2026-10-01', 'ends_at' => '2026-11-01', 'repeat_policy' => 'interval', 'repeat_interval' => $interval, 'repeat_unit' => $unit]);
    $shownAt = CarbonImmutable::parse($shown, 'UTC');
    $closedAt = $closed === null ? null : CarbonImmutable::parse($closed, 'UTC');
    $instant = CarbonImmutable::parse($at, 'UTC');
    $service = app(PopupFrontendService::class);

    expect($service->shouldShow($popup, $closedAt, $shownAt, $instant))->toBe($expected);
    expect($service->eligible([$popup->id => ['closed_at' => $closedAt, 'last_shown_at' => $shownAt]], $instant)->modelKeys())->toBe($expected ? [$popup->id] : []);
})->with([
    'hour just before expiry' => ['hours', 2, '2026-10-19 10:00:00', null, '2026-10-19 11:59:59', false],
    'hour exact expiry' => ['hours', 2, '2026-10-19 10:00:00', null, '2026-10-19 12:00:00', true],
    'later close restarts interval' => ['hours', 2, '2026-10-19 09:00:00', '2026-10-19 10:30:00', '2026-10-19 12:00:00', false],
    'later display restarts interval' => ['hours', 2, '2026-10-19 11:00:00', '2026-10-19 09:00:00', '2026-10-19 12:00:00', false],
    'day before expiry' => ['days', 1, '2026-10-18 12:00:00', null, '2026-10-19 11:59:59', false],
    'day exact expiry' => ['days', 1, '2026-10-18 12:00:00', null, '2026-10-19 12:00:00', true],
    'future visitor event' => ['hours', 1, '2026-10-20 12:00:00', null, '2026-10-19 12:00:00', false],
]);

test('interval policy permits a first visit and handles close only state and invalid policy safely', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00', 'UTC'));
    $popup = Popup::factory()->create(['repeat_policy' => 'interval', 'repeat_interval' => 1, 'repeat_unit' => 'hours']);
    $service = app(PopupFrontendService::class);
    expect($service->shouldShow($popup))->toBeTrue();
    expect($service->shouldShow($popup, closedAt: now()->subMinutes(59)))->toBeFalse();
    expect($service->shouldShow($popup, closedAt: now()->subHour()))->toBeTrue();
    $popup->repeat_interval = 0;
    expect($service->shouldShow($popup))->toBeFalse();
    $popup->repeat_policy = 'unknown';
    expect($service->shouldShow($popup))->toBeFalse();
});

test('popup reading returns empty results while disabled without querying content', function () {
    $disk = Storage::fake('popup-status');
    $disk->put('statuses.json', json_encode(['Popup' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $service = app(PopupFrontendService::class);
    expect($service->published())->toBeEmpty();
    expect($service->eligible())->toBeEmpty();
    expect($service->shouldShow(Popup::factory()->make()))->toBeFalse();
});

test('popup reading returns empty results before its table is installed', function () {
    Schema::dropIfExists('popups');
    $service = app(PopupFrontendService::class);
    expect($service->published())->toBeEmpty();
    expect($service->eligible())->toBeEmpty();
});

test('popup reading returns empty results when module metadata is absent', function () {
    $disk = Storage::fake('missing-popup');
    Module::swap(new LaravelFileRepository(app(), $disk->path('Modules')));
    expect(app(PopupFrontendService::class)->published())->toBeEmpty();
});

test('publication compares explicit instants in the application timezone', function () {
    config(['app.timezone' => 'Europe/Istanbul']);
    $popup = Popup::factory()->create(['starts_at' => '2026-10-19 12:00:00', 'ends_at' => '2026-10-19 14:00:00']);
    $service = app(PopupFrontendService::class);
    expect($service->published(CarbonImmutable::parse('2026-10-19 09:00:00', 'UTC'))->modelKeys())->toBe([$popup->id]);
    expect($service->shouldShow($popup, at: CarbonImmutable::parse('2026-10-19 11:00:00', 'UTC')))->toBeFalse();
});

test('popup frontend selects the requested language then falls back to Turkish', function () {
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $translated = Popup::factory()->create();
    $translated->translations()->create(['lang_id' => $english->id, 'title' => 'English announcement', 'content' => '<p>English</p>']);
    $turkishOnly = Popup::factory()->create();
    $turkishOnly->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->update(['title' => 'Türkçe fallback']);
    $service = app(PopupFrontendService::class);

    $results = $service->published(language: 'en')->keyBy('id');
    expect($results[$translated->id]->title)->toBe('English announcement');
    expect($results[$turkishOnly->id]->title)->toBe('Türkçe fallback');
    expect($service->shouldShow($translated, language: 'en'))->toBeTrue();
});

test('popup without requested or Turkish translation is not published or eligible', function () {
    Language::query()->create(['code' => 'en', 'is_active' => true]);
    $popup = Popup::factory()->create();
    $popup->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->delete();
    $service = app(PopupFrontendService::class);

    expect($service->published(language: 'en')->modelKeys())->not->toContain($popup->id);
    expect($service->shouldShow($popup, language: 'en'))->toBeFalse();
});
