<?php

namespace Modules\Sector\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Sector\Models\Sector;

class ActiveRecordsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'sector.active',
            'title' => 'Aktif sektörler',
            'icon' => 'category',
            'permissions' => ['sector.view'],
            'default' => false,
            'order' => 390,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Sector::query()->where('is_active', true)->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.sector.index', absolute: false),
        ];
    }
}
