<?php

namespace Modules\Products\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Products\Models\ProductCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'products.categories.count',
            'title' => 'Ürün kategorileri',
            'icon' => 'briefcase',
            'permissions' => ['products.categories.view'],
            'default' => false,
            'order' => 340,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => ProductCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.products.categories.index', absolute: false),
        ];
    }
}
