<?php

use App\Models\Admin\Admin;
use App\Services\Analytics\VisitAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    CarbonImmutable::setTestNow('2026-10-22 10:00:00');
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('repeated visits from an IP within the window increment site and page summaries once', function () {
    $analytics = app(VisitAnalytics::class);
    $analytics->record('203.0.113.8', '/about', 'guest', 'computer');
    $analytics->record('203.0.113.8', '/about', 'guest', 'computer');

    expect(DB::table('visit_analytics')->where('scope_key', VisitAnalytics::SITE_SCOPE)->value('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics')->where('scope_key', '/about')->value('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics_guards')->count())->toBe(2)
        ->and(DB::table('visit_analytics_guards')->where('identity_hash', '203.0.113.8')->exists())->toBeFalse();
});

test('one IP can count different pages once while contributing one site visit in the same window', function () {
    $analytics = app(VisitAnalytics::class);

    $analytics->record('203.0.113.9', '/', 'guest', 'phone');
    $analytics->record('203.0.113.9', '/contact', 'guest', 'phone');

    expect(DB::table('visit_analytics')->where('scope_key', VisitAnalytics::SITE_SCOPE)->value('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics')->where('scope_key', '/')->value('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics')->where('scope_key', '/contact')->value('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics_guards')->count())->toBe(3);
});

test('year series separate visitor types and apply the selected device filter', function () {
    $analytics = app(VisitAnalytics::class);
    $analytics->record('203.0.113.30', '/phone-page', 'guest', 'phone');
    $analytics->record('203.0.113.31', '/desktop-page', 'user', 'computer');

    $data = $analytics->dashboardData(2026, 'phone');

    expect($data['visitors']['guest'][10])->toBe(1)
        ->and($data['visitors']['user'][10])->toBe(1)
        ->and($data['devices']['phone'][10])->toBe(1)
        ->and($data['devices']['computer'][10])->toBe(0)
        ->and($data['months'])->toHaveCount(12);
});

test('a repeated visit after the deduplication window increments existing daily summaries', function () {
    CarbonImmutable::setTestNow('2026-10-22 10:00:00');
    $analytics = app(VisitAnalytics::class);
    $analytics->record('203.0.113.10', '/about', 'user', 'other');

    CarbonImmutable::setTestNow('2026-10-22 10:31:00');
    $analytics->record('203.0.113.10', '/about', 'user', 'other');

    expect(DB::table('visit_analytics')->where('scope_key', VisitAnalytics::SITE_SCOPE)->value('visit_count'))->toBe(2)
        ->and(DB::table('visit_analytics')->where('scope_key', '/about')->value('visit_count'))->toBe(2)
        ->and(DB::table('visit_analytics_guards')->count())->toBe(2);

    CarbonImmutable::setTestNow();
});

test('expired deduplication records and daily analytics older than retention can be pruned', function () {
    $analytics = app(VisitAnalytics::class);
    $now = CarbonImmutable::parse('2026-10-22 10:00:00');
    $analytics->record('203.0.113.11', '/about', 'guest', 'computer');
    DB::table('visit_analytics')->where('scope_key', '/about')->update(['stat_date' => '2024-01-01']);

    expect($analytics->pruneGuards($now->addMinutes(31)))->toBe(2)
        ->and($analytics->pruneStats(CarbonImmutable::parse('2024-02-01')))->toBe(1)
        ->and(DB::table('visit_analytics_guards')->count())->toBe(0)
        ->and(DB::table('visit_analytics')->where('scope_key', '/about')->count())->toBe(0);
});

test('dashboard renders year analytics and the selected year popular page list', function () {
    $analytics = app(VisitAnalytics::class);
    $analytics->record('203.0.113.12', '/about', 'guest', 'phone');
    $this->withoutVite();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.dashboard', ['year' => 2026, 'device' => 'phone']))
        ->assertOk()
        ->assertSee('Bu Ay Ziyaret')
        ->assertSee('Yeni Form Başvuruları')
        ->assertSee('Yıllık Toplam Ziyaretler')
        ->assertSee('Yıllık Cihaz Kullanımları')
        ->assertSee('/about');

    $this->get(route('admin.dashboard.pages', ['year' => 2026]))
        ->assertOk()
        ->assertSee('2026 yılına ait ilk 20 sayfa')
        ->assertSee('/about');

    expect(DB::table('visit_analytics_guards')->count())->toBe(2);
});

test('public tracking ignores query variations and excludes bots and missing pages', function () {
    Route::middleware('web')->get('/analytics-public-test', fn () => response('<html>Page</html>')->header('Content-Type', 'text/html; charset=UTF-8'));

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20', 'HTTP_USER_AGENT' => 'Mozilla/5.0'])
        ->get('/analytics-public-test?source=first')->assertOk();
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20', 'HTTP_USER_AGENT' => 'Mozilla/5.0'])
        ->get('/analytics-public-test?source=second')->assertOk();
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.21', 'HTTP_USER_AGENT' => 'Googlebot/2.1'])
        ->get('/analytics-public-test')->assertOk();
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.22', 'HTTP_USER_AGENT' => 'Mozilla/5.0'])
        ->get('/analytics-missing-test')->assertNotFound();

    expect(DB::table('visit_analytics')->where('scope_key', VisitAnalytics::SITE_SCOPE)->sum('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics')->where('scope_key', '/analytics-public-test')->value('visit_count'))->toBe(1)
        ->and(DB::table('visit_analytics_guards')->count())->toBe(2);
});
