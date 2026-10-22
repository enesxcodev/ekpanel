<?php

namespace Modules\Events\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Events\Models\Event;

class UpcomingEventsWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'events.upcoming',
            'title' => 'Yaklaşan etkinlikler',
            'icon' => 'clock',
            'permissions' => ['events.view'],
            'default' => false,
            'order' => 140,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => Event::query()->where('is_active', true)->where('starts_at', '>=', now())->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.events.index', absolute: false),
        ];
    }
}
