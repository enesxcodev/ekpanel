<?php

namespace Modules\Faqs\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Faqs\Models\FaqCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'faqs.categories.count',
            'title' => 'Soru kategorileri',
            'icon' => 'file-text',
            'permissions' => ['faqs.categories.view'],
            'default' => false,
            'order' => 220,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => FaqCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.faqs.categories.index', absolute: false),
        ];
    }
}
