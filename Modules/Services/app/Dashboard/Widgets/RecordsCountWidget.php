<?php

namespace Modules\Services\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Services\Models\Service;

class RecordsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'services.total',
            'title' => 'Hizmetler',
            'icon' => 'briefcase',
            'permissions' => ['services.view'],
            'default' => false,
            'order' => 400,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Service::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.services.index', absolute: false),
        ];
    }
}
