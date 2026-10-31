<?php

namespace Modules\Menus\Services;

use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class MenusFrontendService
{
    public function __construct(private NavigationService $navigation) {}

    /** @return list<array{label: string, icon: ?string, url: ?string, new_tab: bool, children: array}> */
    public function getTree(string $menuKey, ?string $languageCode = null): array
    {
        if (! Module::find('Menus')?->isEnabled() || ! Schema::hasTable('menus') || ! Schema::hasTable('menu_items') || ! Schema::hasTable('menu_translations') || ! Schema::hasColumn('menu_items', 'lang_id')) {
            return [];
        }

        return $this->navigation->tree($menuKey, $languageCode);
    }
}
