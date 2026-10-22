<?php

namespace Modules\Careers\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Careers\Models\JobApplication;

class NewApplicationsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'careers.applications.new',
            'title' => 'Yeni başvurular',
            'icon' => 'briefcase',
            'permissions' => ['careers.view'],
            'default' => true,
            'order' => 100,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => JobApplication::query()->where('status', 'new')->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.careers.applications.index', absolute: false),
        ];
    }
}
