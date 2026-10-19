<?php

namespace Modules\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menus\DataTables\MenusDataTable;
use Modules\Menus\Http\Requests\ReorderMenuItemsRequest;
use Modules\Menus\Http\Requests\SaveMenuRequest;
use Modules\Menus\Models\Menu;
use Modules\Menus\Services\MenuService;
use Modules\Menus\Services\MenuSources;
use Modules\Menus\Services\NavigationService;

class MenusController extends Controller
{
    public function index(Request $request, MenuSources $sources, NavigationService $navigation): View
    {
        $this->authorize('menus.view');

        $sourceOptions = [];
        if ($request->user('admin')->can('menus.update')) {
            foreach ($sources->all() as $key => $source) {
                $sourceOptions[] = ['key' => $key, 'label' => $source->label(), 'options' => $source->options(), 'supports_children' => $source->supportsChildren()];
            }
        }
        $configuration = [
            'menus' => Menu::query()->withCount('items')->orderBy('name')->get()->map(fn (Menu $menu): array => $this->menuSummary($menu))->all(),
            'sources' => $sourceOptions,
            'routes' => $request->user('admin')->can('menus.update') ? $navigation->routeOptions() : [],
            'storeUrl' => route('admin.menus.store'),
            'initialMenu' => $request->integer('menu'),
            'initialItem' => $request->integer('item'),
            'newMenu' => $request->boolean('new'),
            'permissions' => collect(['create', 'update', 'delete'])->mapWithKeys(fn (string $ability): array => [$ability => $request->user('admin')->can('menus.'.$ability)])->all(),
        ];

        return view('menus::workspace', compact('configuration'));
    }

    public function data(MenusDataTable $dataTable): JsonResponse
    {
        $this->authorize('menus.view');

        return $dataTable->handle();
    }

    public function create(): RedirectResponse
    {
        $this->authorize('menus.create');

        return redirect()->route('admin.menus.index', ['new' => 1]);
    }

    public function store(SaveMenuRequest $request, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $menu = $service->save($request->validated());
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü eklendi',
            'Bir menü eklendi.',
            route('admin.menus.index', ['menu' => $menu->id], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['menu' => $this->menuSummary($menu->loadCount('items')), 'message' => 'Menü başarıyla eklendi.'], 201);
        }

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü başarıyla eklendi.']);
    }

    public function edit(Menu $menu): RedirectResponse
    {
        $this->authorize('menus.update');

        return redirect()->route('admin.menus.index', ['menu' => $menu->id]);
    }

    public function update(SaveMenuRequest $request, Menu $menu, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $service->save($request->validated(), $menu);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü güncellendi',
            'Bir menü güncellendi.',
            route('admin.menus.index', ['menu' => $menu->id], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['menu' => $this->menuSummary($menu->loadCount('items')), 'message' => 'Menü başarıyla güncellendi.']);
        }

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü başarıyla güncellendi.']);
    }

    public function destroy(Request $request, Menu $menu, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $this->authorize('menus.delete');
        $menu->delete();
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü silindi',
            'Bir menü ve bağlı öğeleri silindi.',
            route('admin.menus.index', absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Menü ve öğeleri başarıyla silindi.']);
        }

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü ve öğeleri başarıyla silindi.']);
    }

    public function workspace(Menu $menu, MenuService $service): JsonResponse
    {
        $this->authorize('menus.view');
        $items = [];
        $append = function (array $nodes) use (&$append, &$items): void {
            foreach ($nodes as $node) {
                $items[] = [...$node['item']->only(['id', 'parent_id', 'label', 'icon', 'target_type', 'url', 'route_name', 'route_parameters', 'anchor', 'source', 'source_id', 'dynamic_children', 'is_active', 'new_tab', 'sort_order']),
                    'target' => $node['target'],
                    'update_url' => route('admin.menus.items.update', [$node['item']->menu_id, $node['item']->id]),
                    'delete_url' => route('admin.menus.items.destroy', [$node['item']->menu_id, $node['item']->id]),
                ];
                $append($node['children']);
            }
        };
        $append($service->adminTree($menu));

        return response()->json([
            'menu' => $this->menuSummary($menu->loadCount('items')),
            'items' => $items,
            'urls' => ['update' => route('admin.menus.update', $menu), 'delete' => route('admin.menus.destroy', $menu), 'add' => route('admin.menus.items.store', $menu), 'reorder' => route('admin.menus.reorder', $menu)],
        ]);
    }

    public function reorder(ReorderMenuItemsRequest $request, Menu $menu, MenuService $service): JsonResponse
    {
        $service->reorder($menu, $request->validated('items'));

        return response()->json(['message' => 'Menü sıralaması kaydedildi.']);
    }

    /** @return array<string, mixed> */
    private function menuSummary(Menu $menu): array
    {
        return [...$menu->only(['id', 'name', 'key', 'icon', 'is_active', 'items_count']), 'workspace_url' => route('admin.menus.workspace', $menu)];
    }
}
