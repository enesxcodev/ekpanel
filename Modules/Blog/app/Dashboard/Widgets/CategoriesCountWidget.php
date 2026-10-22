<?php

namespace Modules\Blog\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Blog\Models\BlogCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'blog.categories.count',
            'title' => 'Blog kategorileri',
            'icon' => 'file-text',
            'permissions' => ['blog.categories.view'],
            'default' => false,
            'order' => 40,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => BlogCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.blog.categories.index', absolute: false),
        ];
    }
}
