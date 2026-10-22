<?php

namespace Modules\Faqs\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Faqs\Models\Faq;

class RecordsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'faqs.total',
            'title' => 'Sıkça sorulan sorular',
            'icon' => 'file-text',
            'permissions' => ['faqs.view'],
            'default' => false,
            'order' => 210,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Faq::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.faqs.index', absolute: false),
        ];
    }
}
