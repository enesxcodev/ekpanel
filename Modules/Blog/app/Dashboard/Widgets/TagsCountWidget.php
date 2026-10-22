<?php

namespace Modules\Blog\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Blog\Models\BlogTag;

class TagsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'blog.tags.count',
            'title' => 'Blog etiketleri',
            'icon' => 'file-text',
            'permissions' => ['blog.tags.view'],
            'default' => false,
            'order' => 50,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => BlogTag::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.blog.tags.index', absolute: false),
        ];
    }
}
