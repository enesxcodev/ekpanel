<?php

namespace Modules\Galeriler\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Galeriler\Models\GalerilerItem;

class RecordsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'galeriler.total',
            'title' => 'Galeri içerikleri',
            'icon' => 'photo',
            'permissions' => ['galeriler.view'],
            'default' => false,
            'order' => 230,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => GalerilerItem::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.galeriler.index', absolute: false),
        ];
    }
}
