<?php

namespace Modules\Testimonials\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Testimonials\Models\Testimonial;

class RecordsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'testimonials.total',
            'title' => 'Müşteri görüşleri',
            'icon' => 'mail',
            'permissions' => ['testimonials.view'],
            'default' => false,
            'order' => 460,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Testimonial::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.testimonials.index', absolute: false),
        ];
    }
}
