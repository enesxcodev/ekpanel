<?php

namespace App\Services\Admin\Modules;

use App\Models\Admin\Admin;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ModulePanelService
{
    /** @return list<array<string, mixed>> */
    public function menus(Admin $admin): array
    {
        $items = [];

        foreach (Module::allEnabled() as $module) {
            $items = array_merge($items, $this->filterMenus(
                config(strtolower($module->getName()).'.menus', []),
                $module->get('panel', [])['permissions'] ?? [],
                $admin,
                $module->getName(),
            ));
        }

        usort($items, fn (array $first, array $second): int => $first['order'] <=> $second['order']);

        return $items;
    }

    /**
     * @param  array<string, array<string, mixed>>  $menus
     * @param  list<string>  $permissions
     * @return list<array<string, mixed>>
     */
    private function filterMenus(array $menus, array $permissions, Admin $admin, string $prefix): array
    {
        $items = [];

        foreach ($menus as $key => $menu) {
            if (! is_array($menu) || ! isset($menu['label'])) {
                continue;
            }

            $id = $prefix.'.'.$key;

            if (isset($menu['children'])) {
                $children = $this->filterMenus($menu['children'], $permissions, $admin, $id);

                if ($children === []) {
                    continue;
                }

                $menu['children'] = $children;
                $menu['is_active'] = collect($children)->contains('is_active', true);
            } else {
                if (
                    ! isset($menu['route'], $menu['permission'], $menu['menu_permission'])
                    || ! Route::has($menu['route'])
                    || ! in_array($menu['permission'], $permissions, true)
                    || ! in_array($menu['menu_permission'], $permissions, true)
                    || ! $admin->can($menu['permission'])
                    || ! $admin->can($menu['menu_permission'])
                ) {
                    continue;
                }

                $menu['is_active'] = request()->routeIs($menu['active'] ?? $menu['route']);
            }

            $items[] = [...$menu, 'id' => 'module-menu-'.md5($id), 'order' => (int) ($menu['order'] ?? 100)];
        }

        usort($items, fn (array $first, array $second): int => $first['order'] <=> $second['order']);

        return $items;
    }

    /** @return array<string, string> */
    public function permissionLabels(): array
    {
        $labels = [];

        foreach (Module::allEnabled() as $module) {
            $this->collectPermissionLabels(config(strtolower($module->getName()).'.menus', []), '', $labels);
        }

        return $labels;
    }

    /**
     * @param  array<string, array<string, mixed>>  $menus
     * @param  array<string, string>  $labels
     */
    private function collectPermissionLabels(array $menus, string $parent, array &$labels): void
    {
        foreach ($menus as $menu) {
            if (! is_array($menu) || ! isset($menu['label'])) {
                continue;
            }

            $label = $parent === '' ? $menu['label'] : $parent.' / '.$menu['label'];

            if (isset($menu['children'])) {
                $this->collectPermissionLabels($menu['children'], $label, $labels);
            } elseif (isset($menu['menu_permission'])) {
                $labels[str($menu['menu_permission'])->beforeLast('.')->toString()] = $label;
            }
        }
    }

    public function syncPermissions(): int
    {
        $count = 0;

        foreach (Module::allEnabled() as $module) {
            $panel = $module->get('panel', []);

            foreach ($panel['permissions'] ?? [] as $name) {
                Permission::findOrCreate($name, 'admin');
                $count++;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $count;
    }
}
