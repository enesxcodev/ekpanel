<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Events\Dashboard\Widgets\RegistrationsCountWidget;
use Modules\Events\Dashboard\Widgets\UpcomingEventsWidget;
use Modules\Events\Database\Factories\EventRegistrationFactory;
use Modules\Events\Models\Event;

uses(LazilyRefreshDatabase::class);

test('events.upcoming widget reports the domain count and excludes ineligible records', function () {
    $this->freezeTime();
    Event::factory()->create(['is_active' => true, 'starts_at' => now()->addHour()]);
    Event::factory()->create(['is_active' => true, 'starts_at' => now()->subHour()]);
    Event::factory()->create(['is_active' => false, 'starts_at' => now()->addHour()]);

    $result = (new UpcomingEventsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});

test('events.registrations.count widget reports the domain count and excludes ineligible records', function () {
    EventRegistrationFactory::new()->create(['status' => 'registered']);
    EventRegistrationFactory::new()->create(['status' => 'attended']);
    EventRegistrationFactory::new()->create(['status' => 'cancelled']);

    $result = (new RegistrationsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
