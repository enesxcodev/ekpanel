<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Appointments\Models\Appointment;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $disk = Storage::fake('appointments-test-statuses');
    $disk->put('statuses.json', json_encode(['Appointments' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('an admin can create an appointment', function () {
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.appointments.store'), [
        'customer_name' => 'Ayşe Yılmaz', 'customer_email' => 'ayse@example.test', 'service_name' => 'Danışmanlık',
        'starts_at' => '2026-11-01 10:30:00', 'duration_minutes' => 30, 'status' => 'pending',
    ])->assertRedirectToRoute('admin.appointments.index');

    $this->assertDatabaseHas('appointments', ['customer_name' => 'Ayşe Yılmaz', 'service_name' => 'Danışmanlık']);
});

test('appointment input validates the allowed duration', function () {
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.appointments.store'), [
        'customer_name' => 'Ayşe Yılmaz', 'service_name' => 'Danışmanlık', 'starts_at' => now()->addDay()->toDateTimeString(),
        'duration_minutes' => 1, 'status' => 'pending',
    ])->assertSessionHasErrors('duration_minutes');
});

test('appointment listing provides the data table endpoint', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $appointment = Appointment::factory()->create();

    $this->actingAs($admin, 'admin')->getJson(route('admin.appointments.data', [
        'draw' => 1, 'start' => 0, 'length' => 10,
        'columns' => [['data' => 'customer_name', 'name' => 'customer_name', 'searchable' => 'true', 'orderable' => 'true']],
    ]))->assertOk()->assertJsonPath('data.0.id', $appointment->id);
});
