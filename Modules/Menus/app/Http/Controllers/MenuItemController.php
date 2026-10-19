<?php

namespace Modules\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menus\Http\Requests\SaveMenuItemRequest;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Services\MenuService;

class MenuItemController extends Controller
{
    public function create(Menu $menu): RedirectResponse
    {
        $this->authorize('menus.update');

        return redirect()->route('admin.menus.index', ['menu' => $menu->id]);
    }

    public function store(SaveMenuItemRequest $request, Menu $menu, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $item = $service->saveItem($menu, $request->validated());
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü öğesi eklendi',
            'Bir menü öğesi eklendi.',
            route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['id' => $item->id, 'message' => 'Menü öğesi başarıyla eklendi.'], 201);
        }

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü öğesi başarıyla eklendi.']);
    }

    public function edit(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->authorize('menus.update');

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id]);
    }

    public function update(SaveMenuItemRequest $request, Menu $menu, MenuItem $item, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $service->saveItem($menu, $request->validated(), $item);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü öğesi güncellendi',
            'Bir menü öğesi güncellendi.',
            route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['id' => $item->id, 'message' => 'Menü öğesi başarıyla güncellendi.']);
        }

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü öğesi başarıyla güncellendi.']);
    }

    public function destroy(Request $request, Menu $menu, MenuItem $item, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $this->authorize('menus.update');
        $deleted = $service->deleteItem($menu, $item);
        if ($deleted) {
            $notifications->sendToAdminsForActor(
                $request->user('admin'),
                'Menü öğesi silindi',
                'Bir menü öğesi silindi.',
                route('admin.menus.index', ['menu' => $menu->id], absolute: false),
            );
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $deleted ? 'Menü öğesi başarıyla silindi.' : 'Önce alt öğeleri taşıyın veya silin.'], $deleted ? 200 : 422);
        }

        return redirect()->route('admin.menus.index')->with('notification', [
            'type' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Menü öğesi başarıyla silindi.' : 'Önce alt öğeleri taşıyın veya silin.',
        ]);
    }
}
