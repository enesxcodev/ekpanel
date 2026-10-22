<?php

use App\Models\Admin\Admin;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Appointments\Dashboard\Widgets\PendingAppointmentsWidget;
use Modules\Appointments\Dashboard\Widgets\TodayAppointmentsWidget;
use Modules\Appointments\Models\Appointment;

uses(LazilyRefreshDatabase::class);

test('appointments.today widget reports the domain count and excludes ineligible records', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-22 12:00:00', config('app.timezone')));
    Appointment::factory()->create(['starts_at' => now()->startOfDay(), 'status' => 'pending']);
    Appointment::factory()->create(['starts_at' => now()->endOfDay(), 'status' => 'confirmed']);
    Appointment::factory()->create(['starts_at' => now()->startOfDay()->subSecond(), 'status' => 'pending']);
    Appointment::factory()->create(['starts_at' => now()->addDay()->startOfDay(), 'status' => 'pending']);
    Appointment::factory()->create(['starts_at' => now(), 'status' => 'cancelled']);

    $result = (new TodayAppointmentsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('appointments.pending widget reports the domain count and excludes ineligible records', function () {
    Appointment::factory()->create(['status' => 'pending']);
    Appointment::factory()->create(['status' => 'confirmed']);
    Appointment::factory()->create(['status' => 'cancelled']);

    $result = (new PendingAppointmentsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
