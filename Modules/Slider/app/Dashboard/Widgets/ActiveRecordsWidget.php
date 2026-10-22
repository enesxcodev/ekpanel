<?php

namespace Modules\Slider\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Slider\Models\Slider;

class ActiveRecordsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'slider.active',
            'title' => 'Aktif slider içerikleri',
            'icon' => 'photo',
            'permissions' => ['slider.view'],
            'default' => false,
            'order' => 430,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Slider::query()->where('is_active', true)->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.slider.index', absolute: false),
        ];
    }
}
