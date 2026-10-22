<?php

namespace Modules\Documents\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Documents\Models\Document;

class DocumentsCountWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'documents.total',
            'title' => 'Belgeler',
            'icon' => 'folder',
            'permissions' => ['documents.view'],
            'default' => true,
            'order' => 60,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Document::query()->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.documents.index', absolute: false),
        ];
    }
}
