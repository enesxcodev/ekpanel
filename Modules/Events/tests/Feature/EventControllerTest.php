<?php

use App\Models\Admin\Admin;
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

    $this->actingAs($admin, 'admin')->post(route('admin.events.store'), [
        'title' => 'Laravel Buluşması', 'starts_at' => '2026-11-10 18:00:00', 'capacity' => 20, 'is_active' => '1',
    ])->assertRedirectToRoute('admin.events.index');

    $this->assertDatabaseHas('events', ['title' => 'Laravel Buluşması', 'capacity' => 20]);
});

test('registration rejects a full event', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $event = Event::factory()->create(['capacity' => 1]);
    $event->registrations()->create(['attendee_name' => 'İlk Kayıt', 'email' => 'ilk@example.test', 'status' => 'registered']);

    $this->actingAs($admin, 'admin')->post(route('admin.events.registrations.store'), [
        'event_id' => $event->id, 'attendee_name' => 'İkinci Kayıt', 'email' => 'ikinci@example.test', 'status' => 'registered',
    ])->assertSessionHasErrors('event_id');
});
