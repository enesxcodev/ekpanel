<?php

namespace Modules\Menus\Services;

use App\Models\Settings\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Models\MenuTranslation;

class MenuService
{
    public function __construct(private NavigationService $navigation) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Menu $menu = null): Menu
    {
        $language = $this->activeLanguage($data['lang_id'] ?? null);

        return DB::transaction(function () use ($data, $menu, $language): Menu {
            $isNew = $menu === null;
            $menu = $isNew ? new Menu : Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $translation = $menu->translations()->where('lang_id', $language->id)->first() ?? new MenuTranslation(['lang_id' => $language->id]);
            $menuData = ['key' => $data['key'], 'icon' => $data['icon'] ?? null, 'is_active' => (bool) $data['is_active']];

            if ($language->code === MenusLanguageService::PrimaryCode) {
                $menuData['name'] = $data['name'];
            } elseif ($isNew) {
                $menuData['name'] = $data['key'];
            }

            $menu->fill($menuData)->save();
            $translation->fill(['menu_id' => $menu->id, 'lang_id' => $language->id, 'name' => $data['name']])->save();

            return $menu->load(['translations.language']);
        });
    }

    /** @param array<string, mixed> $data */
    public function saveItem(Menu $menu, array $data, ?MenuItem $item = null): MenuItem
    {
        $language = $this->activeLanguage($data['lang_id'] ?? null);

        return DB::transaction(function () use ($menu, $data, $item, $language): MenuItem {
            $menu = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            if (! $menu->translations()->where('lang_id', $language->id)->exists()) {
                throw ValidationException::withMessages(['lang_id' => 'Önce bu dilde menü adını kaydedin.']);
            }

            $items = $menu->items()->where('lang_id', $language->id)->get()->keyBy('id');
            if ($item !== null) {
                $item = $items->get($item->id);
                abort_if($item === null, 404);
            }
            $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
            $parent = $parentId === null ? null : $items->get($parentId);
            if ($parentId !== null && $parent === null) {
                throw ValidationException::withMessages(['parent_id' => 'Bu menü ve dildeki bir üst öğeyi seçin.']);
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
                'lang_id' => $language->id,
                'parent_id' => $parentId,
                'label' => $data['label'],
                'icon' => $data['icon'] ?? null,
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

    public function deleteItem(Menu $menu, MenuItem $item, ?int $languageId = null): bool
    {
        $language = $this->activeLanguage($languageId);

        return DB::transaction(function () use ($menu, $item, $language): bool {
            $lockedMenu = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $item = $lockedMenu->items()->where('lang_id', $language->id)->findOrFail($item->id);
            if ($lockedMenu->items()->where('lang_id', $language->id)->where('parent_id', $item->id)->exists()) {
                return false;
            }
            $item->delete();

            return true;
        });
    }

    /** @param list<array{id: int, parent_id: ?int, sort_order: int}> $positions */
    public function reorder(Menu $menu, array $positions, ?int $languageId = null): void
    {
        $language = $this->activeLanguage($languageId);

        DB::transaction(function () use ($menu, $positions, $language): void {
            $menu = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $items = $menu->items()->where('lang_id', $language->id)->get()->keyBy('id');
            $proposed = collect($positions)->keyBy('id');
            if ($proposed->count() !== $items->count() || $proposed->keys()->diff($items->keys())->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'Bu dilin menü yapısı değişmiş olabilir. Güncel yapıyı yükleyip yeniden deneyin.']);
            }
            foreach ($positions as $position) {
                $parentId = isset($position['parent_id']) ? (int) $position['parent_id'] : null;
                $parent = $parentId === null ? null : $items->get($parentId);
                if (($parentId !== null && $parent === null) || $parent?->dynamic_children) {
                    throw ValidationException::withMessages(['items' => 'Üst öğe aynı menü ve dile ait olmalı, otomatik alt öğe üretmemelidir.']);
                }
                $visited = [(int) $position['id'] => true];
                $cursor = $parentId;
                while ($cursor !== null) {
                    if (isset($visited[$cursor]) || ! $proposed->has($cursor)) {
                        throw ValidationException::withMessages(['items' => 'Öğe kendisinin veya alt öğelerinin altına taşınamaz.']);
                    }
                    $visited[$cursor] = true;
                    $cursor = isset($proposed[$cursor]['parent_id']) ? (int) $proposed[$cursor]['parent_id'] : null;
                }
            }
            foreach ($positions as $position) {
                $items[$position['id']]->fill([
                    'parent_id' => $position['parent_id'],
                    'sort_order' => $position['sort_order'],
                ])->save();
            }
        });
    }

    /** @return list<array{item: MenuItem, target: array{url: ?string, error: ?string}, children: array}> */
    public function adminTree(Menu $menu, ?int $languageId = null): array
    {
        $language = $this->activeLanguage($languageId);
        $byParent = $menu->items()->where('lang_id', $language->id)->get()->groupBy(fn (MenuItem $item): string => (string) ($item->parent_id ?? 0));
        $append = function (?int $parentId, array $visited = []) use (&$append, $byParent, $language): array {
            $nodes = [];
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $item) {
                if (isset($visited[$item->id])) {
                    continue;
                }
                $nextVisited = $visited;
                $nextVisited[$item->id] = true;
                $nodes[] = ['item' => $item, 'target' => $this->navigation->target($item, $language->code), 'children' => $append($item->id, $nextVisited)];
            }

            return $nodes;
        };

        return $append(null);
    }

    /** @return list<array{value: int, label: string}> */
    public function parentOptions(Menu $menu, ?MenuItem $excluded = null, ?int $languageId = null): array
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
        $append($this->adminTree($menu, $languageId));

        return $options;
    }

    private function activeLanguage(mixed $languageId): Language
    {
        $languageId ??= Language::query()->where('code', MenusLanguageService::PrimaryCode)->where('is_active', true)->value('id');

        return Language::query()->whereKey($languageId)->where('is_active', true)->firstOrFail();
    }
}
