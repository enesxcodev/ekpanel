<?php

namespace Modules\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Services\NotifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menus\Http\Requests\SaveMenuItemRequest;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Services\MenuService;
use Modules\Menus\Services\MenusLanguageService;

class MenuItemController extends Controller
{
    public function create(Request $request, Menu $menu): RedirectResponse
    {
        $this->authorize('menus.update');

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'lang' => $request->query('lang', 'tr')]);
    }

    public function store(SaveMenuItemRequest $request, Menu $menu, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $item = $service->saveItem($menu, $request->validated());
        $language = Language::query()->findOrFail($item->lang_id);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü öğesi eklendi',
            'Bir menü öğesi eklendi.',
            route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id, 'lang' => $language->code], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['id' => $item->id, 'lang_id' => $item->lang_id, 'message' => 'Menü öğesi başarıyla eklendi.'], 201);
        }

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Menü öğesi başarıyla eklendi.']);
    }

    public function edit(Request $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->authorize('menus.update');

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id, 'lang' => $request->query('lang', 'tr')]);
    }

    public function update(SaveMenuItemRequest $request, Menu $menu, MenuItem $item, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $service->saveItem($menu, $data, $item);
        $language = Language::query()->findOrFail($data['lang_id']);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü öğesi güncellendi',
            'Bir menü öğesi güncellendi.',
            route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id, 'lang' => $language->code], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['id' => $item->id, 'lang_id' => $item->lang_id, 'message' => 'Menü öğesi başarıyla güncellendi.']);
        }

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'item' => $item->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Menü öğesi başarıyla güncellendi.']);
    }

    public function destroy(Request $request, Menu $menu, MenuItem $item, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $this->authorize('menus.update');
        $language = app(MenusLanguageService::class)->selectedAdmin($request);
        abort_if($language === null || (int) $request->input('lang_id', $language->id) !== $language->id, 422, 'Form dili ile istek dili eşleşmiyor.');
        $deleted = $service->deleteItem($menu, $item, $language->id);
        if ($deleted) {
            $notifications->sendToAdminsForActor(
                $request->user('admin'),
                'Menü öğesi silindi',
                'Bir menü öğesi silindi.',
                route('admin.menus.index', ['menu' => $menu->id, 'lang' => $language->code], absolute: false),
            );
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $deleted ? 'Menü öğesi başarıyla silindi.' : 'Önce alt öğeleri taşıyın veya silin.'], $deleted ? 200 : 422);
        }

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'lang' => $language->code])->with('notification', [
            'type' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Menü öğesi başarıyla silindi.' : 'Önce alt öğeleri taşıyın veya silin.',
        ]);
    }
}
