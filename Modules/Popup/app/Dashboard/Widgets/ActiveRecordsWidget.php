<?php

namespace Modules\Popup\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Popup\Models\Popup;

class ActiveRecordsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'popup.active',
            'title' => 'Aktif popup içerikleri',
            'icon' => 'eye',
            'permissions' => ['popup.view'],
            'default' => false,
            'order' => 310,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Popup::query()->where('is_active', true)->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.popup.index', absolute: false),
        ];
    }
}
