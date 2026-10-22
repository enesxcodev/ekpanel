<?php

namespace Modules\Appointments\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Appointments\Models\Appointment;
use Yajra\DataTables\Facades\DataTables;

class AppointmentsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Appointment::query()->select(['id', 'customer_name', 'service_name', 'starts_at', 'duration_minutes', 'status']))
            ->editColumn('starts_at', fn (Appointment $appointment): string => $appointment->starts_at->format('d.m.Y H:i'))
            ->editColumn('status', fn (Appointment $appointment): string => match ($appointment->status) {
                'confirmed' => 'Onaylandı',
                'cancelled' => 'İptal edildi',
                default => 'Bekliyor',
            })
            ->addColumn('actions', fn (Appointment $appointment): string => view('appointments::partials.actions', compact('appointment'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
