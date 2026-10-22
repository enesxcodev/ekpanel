<?php

namespace Modules\Pages\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Pages\Models\PageCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'pages.categories.count',
            'title' => 'Sayfa kategorileri',
            'icon' => 'file-text',
            'permissions' => ['pages.categories.view'],
            'default' => false,
            'order' => 290,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => PageCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.pages.categories.index', absolute: false),
        ];
    }
}
