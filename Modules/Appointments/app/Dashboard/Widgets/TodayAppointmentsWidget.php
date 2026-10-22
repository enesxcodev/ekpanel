<?php

namespace Modules\Appointments\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Appointments\Models\Appointment;

class TodayAppointmentsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'appointments.today',
            'title' => 'Bugünkü randevular',
            'icon' => 'clock',
            'permissions' => ['appointments.view'],
            'default' => true,
            'order' => 80,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Appointment::query()->where('starts_at', '>=', now()->startOfDay())->where('starts_at', '<', now()->addDay()->startOfDay())->where('status', '!=', 'cancelled')->count(),
            'description' => 'İptal edilen randevular hariç',
            'url' => route('admin.appointments.index', absolute: false),
        ];
    }
}
