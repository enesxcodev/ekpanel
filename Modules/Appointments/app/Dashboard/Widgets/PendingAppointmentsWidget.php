<?php

namespace Modules\Appointments\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Appointments\Models\Appointment;

class PendingAppointmentsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'appointments.pending',
            'title' => 'Bekleyen randevular',
            'icon' => 'clock',
            'permissions' => ['appointments.view'],
            'default' => false,
            'order' => 90,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Appointment::query()->where('status', 'pending')->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.appointments.index', absolute: false),
        ];
    }
}
