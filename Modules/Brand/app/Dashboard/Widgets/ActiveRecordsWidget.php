<?php

namespace Modules\Brand\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Brand\Models\Brand;

class ActiveRecordsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'brand.active',
            'title' => 'Aktif markalar',
            'icon' => 'star',
            'permissions' => ['brand.view'],
            'default' => false,
            'order' => 200,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Brand::query()->where('is_active', true)->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.brand.index', absolute: false),
        ];
    }
}
