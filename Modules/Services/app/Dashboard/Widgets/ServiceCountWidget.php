<?php

namespace Modules\Services\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Services\Models\Service;

class ServiceCountWidget implements DashboardWidget
{
    public function definition(): array
    {
        return ['id' => 'services.active', 'title' => 'Aktif hizmetler', 'icon' => 'briefcase',
            'permissions' => ['services.view'], 'default' => false, 'order' => 100, 'type' => 'stat'];
    }

    public function data(Admin $admin): array
    {
        return ['value' => Service::query()->where('is_active', true)->count(), 'url' => route('admin.services.index', absolute: false)];
    }
}
