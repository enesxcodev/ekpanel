<?php

namespace Modules\Documents\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Documents\Models\DocumentCategory;

class CategoriesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'documents.categories.count',
            'title' => 'Belge kategorileri',
            'icon' => 'folder',
            'permissions' => ['documents.categories.view'],
            'default' => false,
            'order' => 70,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => DocumentCategory::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.documents.categories.index', absolute: false),
        ];
    }
}
