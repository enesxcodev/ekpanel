<?php

namespace Modules\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Menus\Http\Requests\SaveMenuItemRequest;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Services\MenuService;
use Modules\Menus\Services\MenuSources;
use Modules\Menus\Services\NavigationService;

class MenuItemController extends Controller
{
    public function create(Menu $menu, MenuService $service, MenuSources $sources, NavigationService $navigation): View
    {
        $this->authorize('menus.update');

        return view('menus::items.create', $this->formData($menu, null, $service, $sources, $navigation));
    }

    public function store(SaveMenuItemRequest $request, Menu $menu, MenuService $service): RedirectResponse
    {
        $service->saveItem($menu, $request->validated());

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü öğesi başarıyla eklendi.']);
    }

    public function edit(Menu $menu, MenuItem $item, MenuService $service, MenuSources $sources, NavigationService $navigation): View
    {
        $this->authorize('menus.update');

        return view('menus::items.edit', $this->formData($menu, $item, $service, $sources, $navigation));
    }

    public function update(SaveMenuItemRequest $request, Menu $menu, MenuItem $item, MenuService $service): RedirectResponse
    {
        $service->saveItem($menu, $request->validated(), $item);

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü öğesi başarıyla güncellendi.']);
    }

    public function destroy(Menu $menu, MenuItem $item, MenuService $service): RedirectResponse
    {
        $this->authorize('menus.update');
        $deleted = $service->deleteItem($menu, $item);

        return redirect()->route('admin.menus.index')->with('notification', [
            'type' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Menü öğesi başarıyla silindi.' : 'Önce alt öğeleri taşıyın veya silin.',
        ]);
    }

    /** @return array<string, mixed> */
    private function formData(Menu $menu, ?MenuItem $item, MenuService $service, MenuSources $sources, NavigationService $navigation): array
    {
        $sourceOptions = [];
        foreach ($sources->all() as $key => $source) {
            $sourceOptions[$key] = ['label' => $source->label(), 'options' => $source->options(), 'supports_children' => $source->supportsChildren()];
        }

        return [
            'menu' => $menu,
            ...($item === null ? [] : ['item' => $item]),
            'parentOptions' => $service->parentOptions($menu, $item),
            'sourceOptions' => $sourceOptions,
            'routeOptions' => $navigation->routeOptions(),
        ];
    }
}
