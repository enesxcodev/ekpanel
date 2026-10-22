<?php

namespace Modules\Branches\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Branches\Models\BranchCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'branches.categories.count',
            'title' => 'Şube kategorileri',
            'icon' => 'category',
            'permissions' => ['branches.categories.view'],
            'default' => false,
            'order' => 180,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => BranchCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.branches.categories.index', absolute: false),
        ];
    }
}
