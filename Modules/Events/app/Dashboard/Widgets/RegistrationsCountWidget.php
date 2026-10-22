<?php

namespace Modules\Events\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Events\Models\EventRegistration;

class RegistrationsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'events.registrations.count',
            'title' => 'Etkinlik kayıtları',
            'icon' => 'clock',
            'permissions' => ['events.view'],
            'default' => false,
            'order' => 150,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => EventRegistration::query()->where('status', '!=', 'cancelled')->count(),
            'description' => 'İptal edilen kayıtlar hariç',
            'url' => route('admin.events.registrations.index', absolute: false),
        ];
    }
}
