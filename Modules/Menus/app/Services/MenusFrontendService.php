<?php

namespace Modules\Menus\Services;

use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class MenusFrontendService
{
    public function __construct(private NavigationService $navigation) {}

    /** @return list<array{label: string, icon: ?string, url: ?string, new_tab: bool, children: array}> */
    public function getTree(string $menuKey): array
    {
        if (! Module::find('Menus')?->isEnabled() || ! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return [];
        }

        return $this->navigation->tree($menuKey);
    }
}
