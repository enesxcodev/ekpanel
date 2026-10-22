<?php

namespace Modules\Konum\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Konum\Models\City;

class CitiesCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'konum.cities.count',
            'title' => 'Konum verisindeki iller',
            'icon' => 'category',
            'permissions' => ['konum.view'],
            'default' => false,
            'order' => 480,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => City::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.konum.index', absolute: false),
        ];
    }
}
