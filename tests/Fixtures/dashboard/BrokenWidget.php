<?php

namespace Modules\WidgetFixture\Dashboard\Widgets;

use App\Models\Admin\Admin;
use RuntimeException;

class BrokenWidget extends CountWidget
{
    public function definition(): array
    {
        return [...parent::definition(), 'id' => 'widgetfixture.broken', 'title' => 'Broken count', 'order' => 3];
    }

    public function data(Admin $admin): array
    {
        throw new RuntimeException('Private database detail');
    }
}
