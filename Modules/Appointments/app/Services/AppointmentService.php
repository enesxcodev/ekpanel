<?php

namespace Modules\Appointments\Services;

use Illuminate\Support\Facades\DB;
use Modules\Appointments\Models\Appointment;

class AppointmentService
{
    /** @param array<string, mixed> $data */
    public function save(array $data, ?Appointment $appointment = null): Appointment
    {
        return DB::transaction(function () use ($data, $appointment): Appointment {
            $appointment ??= new Appointment;
            $appointment->fill($data)->save();

            return $appointment;
        });
    }

    public function delete(Appointment $appointment): void
    {
        DB::transaction(fn () => $appointment->delete());
    }
}
