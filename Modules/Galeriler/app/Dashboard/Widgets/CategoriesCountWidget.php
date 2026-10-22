<?php

namespace Modules\Galeriler\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Galeriler\Models\GalerilerCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'galeriler.categories.count',
            'title' => 'Galeri kategorileri',
            'icon' => 'photo',
            'permissions' => ['galeriler.categories.view'],
            'default' => false,
            'order' => 240,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => GalerilerCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.galeriler.categories.index', absolute: false),
        ];
    }
}
