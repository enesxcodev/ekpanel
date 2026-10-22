<?php

namespace Modules\Users\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Users\Models\User;

class ActiveUsersWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'users.active',
            'title' => 'Aktif kullanıcı',
            'icon' => 'users',
            'permissions' => ['users.view'],
            'default' => false,
            'order' => 20,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => User::query()->where('is_active', true)->count(),
            'description' => 'İlgili yönetim ekranını görüntüleyin',
            'url' => route('admin.web-users.index', absolute: false),
        ];
    }
}
