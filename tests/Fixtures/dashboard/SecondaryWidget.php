<?php

namespace Modules\WidgetFixture\Dashboard\Widgets;

use App\Models\Admin\Admin;

class SecondaryWidget extends CountWidget
{
    public function definition(): array
    {
        return [...parent::definition(), 'id' => 'widgetfixture.secondary', 'title' => 'Secondary count', 'permissions' => ['widgetfixture.secondary.view'], 'order' => 2];
    }

    public function data(Admin $admin): array
    {
        return ['value' => 12];
    }
}
