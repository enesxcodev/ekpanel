<?php

namespace Modules\Menus\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;

class MenuService
{
    public function __construct(private NavigationService $navigation) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Menu $menu = null): Menu
    {
        $menu ??= new Menu;
        $menu->fill($data)->save();

        return $menu;
    }

    /** @param array<string, mixed> $data */
    public function saveItem(Menu $menu, array $data, ?MenuItem $item = null): MenuItem
    {
        return DB::transaction(function () use ($menu, $data, $item): MenuItem {
            Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $items = $menu->items()->get()->keyBy('id');
            if ($item !== null) {
                $item = $items->get($item->id);
                abort_if($item === null, 404);
            }
            $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
            $parent = $parentId === null ? null : $items->get($parentId);
            if ($parentId !== null && $parent === null) {
                throw ValidationException::withMessages(['parent_id' => 'Bu menüye ait bir üst öğe seçin.']);
            }
            $cursor = $parent;
            $visited = [];
            while ($cursor !== null) {
                if ($cursor->id === $item?->id || isset($visited[$cursor->id])) {
                    throw ValidationException::withMessages(['parent_id' => 'Öğe kendisinin veya alt öğelerinin altına taşınamaz.']);
                }
                $visited[$cursor->id] = true;
                $cursor = $items->get($cursor->parent_id);
            }
            if ($parent?->dynamic_children) {
                throw ValidationException::withMessages(['parent_id' => 'Otomatik alt öğe üreten bir öğeye manuel çocuk eklenemez.']);
            }
            if (! empty($data['dynamic_children']) && $item !== null && $items->contains('parent_id', $item->id)) {
                throw ValidationException::withMessages(['dynamic_children' => 'Önce mevcut manuel alt öğeleri taşıyın veya kaldırın.']);
            }
            $type = $data['target_type'];
            $item ??= $menu->items()->make();
            $item->fill([
                'parent_id' => $parentId,
                'label' => $data['label'],
                'target_type' => $type,
                'url' => in_array($type, ['url', 'anchor'], true) ? ($data['url'] ?? null) : null,
                'route_name' => $type === 'route' ? $data['route_name'] : null,
                'route_parameters' => $type === 'route' ? ($data['route_parameters'] ?? []) : null,
                'anchor' => $type === 'anchor' ? $data['anchor'] : null,
                'source' => $type === 'content' ? $data['source'] : null,
                'source_id' => $type === 'content' ? $data['source_id'] : null,
                'dynamic_children' => $type === 'content' && ! empty($data['dynamic_children']),
                'is_active' => (bool) $data['is_active'],
                'new_tab' => (bool) ($data['new_tab'] ?? false),
                'sort_order' => $data['sort_order'],
            ])->save();

            return $item;
        });
    }

    public function deleteItem(Menu $menu, MenuItem $item): bool
    {
        return DB::transaction(function () use ($menu, $item): bool {
            Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $item = $menu->items()->findOrFail($item->id);
            if ($item->children()->exists()) {
                return false;
            }
            $item->delete();

            return true;
        });
    }

    /** @return list<array{item: MenuItem, target: array{url: ?string, error: ?string}, children: array}> */
    public function adminTree(Menu $menu): array
    {
        $byParent = $menu->items()->get()->groupBy(fn (MenuItem $item): string => (string) ($item->parent_id ?? 0));
        $append = function (?int $parentId, array $visited = []) use (&$append, $byParent): array {
            $nodes = [];
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $item) {
                if (isset($visited[$item->id])) {
                    continue;
                }
                $nextVisited = $visited;
                $nextVisited[$item->id] = true;
                $nodes[] = ['item' => $item, 'target' => $this->navigation->target($item), 'children' => $append($item->id, $nextVisited)];
            }

            return $nodes;
        };

        return $append(null);
    }

    /** @return list<array{value: int, label: string}> */
    public function parentOptions(Menu $menu, ?MenuItem $excluded = null): array
    {
        $options = [];
        $append = function (array $nodes, int $depth = 0) use (&$append, &$options, $excluded): void {
            foreach ($nodes as $node) {
                $item = $node['item'];
                if ($item->id === $excluded?->id || $item->dynamic_children) {
                    continue;
                }
                $options[] = ['value' => $item->id, 'label' => str_repeat('— ', $depth).$item->label];
                $append($node['children'], $depth + 1);
            }
        };
        $append($this->adminTree($menu));

        return $options;
    }
}
