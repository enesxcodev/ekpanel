<?php

namespace Modules\Careers\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Careers\Models\JobPosting;

class ActivePostingsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'careers.postings.active',
            'title' => 'Aktif iş ilanları',
            'icon' => 'briefcase',
            'permissions' => ['careers.view'],
            'default' => false,
            'order' => 110,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => JobPosting::query()->where('is_active', true)->where(fn ($query) => $query->whereNull('application_deadline')->orWhere('application_deadline', '>=', today()->toDateString()))->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.careers.index', absolute: false),
        ];
    }
}
