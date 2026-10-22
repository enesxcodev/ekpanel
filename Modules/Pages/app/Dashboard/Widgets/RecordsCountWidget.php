<?php

namespace Modules\Pages\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Pages\Models\Page;

class RecordsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'pages.total',
            'title' => 'Sayfalar',
            'icon' => 'file-text',
            'permissions' => ['pages.view'],
            'default' => false,
            'order' => 270,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Page::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.pages.index', absolute: false),
        ];
    }
}
