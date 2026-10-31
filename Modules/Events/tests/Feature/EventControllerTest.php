<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Events\Models\Event;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $disk = Storage::fake('events-test-statuses');
    $disk->put('statuses.json', json_encode(['Events' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('an admin can create an event', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);

    $this->actingAs($admin, 'admin')->post(route('admin.events.store'), [
        'lang_id' => $language->id,
        'title' => 'Laravel Buluşması', 'starts_at' => '2026-11-10 18:00:00', 'capacity' => 20, 'is_active' => '1',
    ])->assertRedirectToRoute('admin.events.edit', ['event' => 1, 'lang' => 'tr']);

    $this->assertDatabaseHas('event_translations', ['event_id' => 1, 'lang_id' => $language->id, 'title' => 'Laravel Buluşması']);
    $this->assertDatabaseHas('events', ['id' => 1, 'capacity' => 20]);
});

test('event translations change by language while shared schedule and capacity remain unchanged', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $turkish = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $event = Event::query()->create(['starts_at' => '2026-11-10 18:00:00', 'ends_at' => '2026-11-10 20:00:00', 'capacity' => 20, 'is_active' => true]);
    $event->translations()->create(['lang_id' => $turkish->id, 'title' => 'Laravel Buluşması', 'venue' => 'İstanbul', 'description' => 'Türkçe açıklama']);

    $this->actingAs($admin, 'admin')->get(route('admin.events.edit', ['event' => $event->id, 'lang' => 'en']))->assertOk()->assertSee('Çeviri yok');
    $this->actingAs($admin, 'admin')->put(route('admin.events.update', $event), [
        'lang_id' => $english->id,
        'title' => 'Laravel Meetup',
        'venue' => 'London',
        'description' => 'English description',
    ])->assertRedirectToRoute('admin.events.edit', ['event' => $event->id, 'lang' => 'en']);

    $this->assertDatabaseHas('event_translations', ['event_id' => $event->id, 'lang_id' => $turkish->id, 'title' => 'Laravel Buluşması']);
    $this->assertDatabaseHas('event_translations', ['event_id' => $event->id, 'lang_id' => $english->id, 'title' => 'Laravel Meetup']);
    $this->assertDatabaseHas('events', ['id' => $event->id, 'starts_at' => '2026-11-10 18:00:00', 'capacity' => 20, 'is_active' => true]);
});

test('new events cannot be created in a non-Turkish language', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $turkish = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);

    $this->actingAs($admin, 'admin')->post(route('admin.events.store'), [
        'lang_id' => $english->id,
        'title' => 'Invalid language',
        'starts_at' => '2026-11-10 18:00:00',
        'is_active' => true,
    ])->assertSessionHasErrors('lang_id');

    expect($turkish->code)->toBe('tr');
});

test('registration rejects a full event', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $event = Event::factory()->create(['capacity' => 1]);
    $event->registrations()->create(['attendee_name' => 'İlk Kayıt', 'email' => 'ilk@example.test', 'status' => 'registered']);

    $this->actingAs($admin, 'admin')->post(route('admin.events.registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'İkinci Kayıt', 'email' => 'ikinci@example.test', 'status' => 'registered',
    ])->assertSessionHasErrors('event_id');
});
