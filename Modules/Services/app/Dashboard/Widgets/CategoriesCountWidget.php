<?php

namespace Modules\Services\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Services\Models\ServiceCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'services.categories.count',
            'title' => 'Hizmet kategorileri',
            'icon' => 'briefcase',
            'permissions' => ['services.categories.view'],
            'default' => false,
            'order' => 410,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => ServiceCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.services.categories.index', absolute: false),
        ];
    }
}
