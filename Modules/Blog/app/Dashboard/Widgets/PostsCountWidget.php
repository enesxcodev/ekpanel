<?php

namespace Modules\Blog\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Blog\Models\BlogContent;

class PostsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'blog.posts.count',
            'title' => 'Blog yazıları',
            'icon' => 'file-text',
            'permissions' => ['blog.view'],
            'default' => true,
            'order' => 30,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => BlogContent::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.blog.index', absolute: false),
        ];
    }
}
