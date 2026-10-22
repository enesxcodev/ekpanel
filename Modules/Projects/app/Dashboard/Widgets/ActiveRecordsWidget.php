<?php

namespace Modules\Projects\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Projects\Models\Project;

class ActiveRecordsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'projects.active',
            'title' => 'Aktif projeler',
            'icon' => 'folder',
            'permissions' => ['projects.view'],
            'default' => false,
            'order' => 360,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Project::query()->where('is_active', true)->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.projects.index', absolute: false),
        ];
    }
}
