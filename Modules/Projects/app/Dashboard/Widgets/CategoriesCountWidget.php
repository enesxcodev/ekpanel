<?php

namespace Modules\Projects\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Projects\Models\ProjectCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'projects.categories.count',
            'title' => 'Proje kategorileri',
            'icon' => 'folder',
            'permissions' => ['projects.categories.view'],
            'default' => false,
            'order' => 370,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => ProjectCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.projects.categories.index', absolute: false),
        ];
    }
}
