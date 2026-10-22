<?php

namespace Modules\WidgetFixture\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;

class CountWidget implements DashboardWidget
{
    public static int $calls = 0;

    public function definition(): array
    {
        return ['id' => 'widgetfixture.total', 'title' => 'Fixture count', 'icon' => 'users',
            'permissions' => ['widgetfixture.view'], 'default' => true, 'order' => 1, 'type' => 'stat'];
    }

    public function data(Admin $admin): array
    {
        self::$calls++;

        return ['value' => Admin::query()->count(), 'description' => 'Fixture total'];
    }
}
