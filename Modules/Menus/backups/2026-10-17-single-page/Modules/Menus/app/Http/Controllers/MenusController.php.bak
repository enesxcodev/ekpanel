<?php

namespace Modules\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Menus\DataTables\MenusDataTable;
use Modules\Menus\Http\Requests\SaveMenuRequest;
use Modules\Menus\Models\Menu;
use Modules\Menus\Services\MenuService;

class MenusController extends Controller
{
    public function index(): View
    {
        $this->authorize('menus.view');

        return view('menus::index');
    }

    public function data(MenusDataTable $dataTable): JsonResponse
    {
        $this->authorize('menus.view');

        return $dataTable->handle();
    }

    public function create(): View
    {
        $this->authorize('menus.create');

        return view('menus::create');
    }

    public function store(SaveMenuRequest $request, MenuService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü başarıyla eklendi.']);
    }

    public function edit(Menu $menu, MenuService $service): View
    {
        $this->authorize('menus.update');
        $tree = $service->adminTree($menu);

        return view('menus::edit', compact('menu', 'tree'));
    }

    public function update(SaveMenuRequest $request, Menu $menu, MenuService $service): RedirectResponse
    {
        $service->save($request->validated(), $menu);

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü başarıyla güncellendi.']);
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('menus.delete');
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('notification', ['type' => 'success', 'message' => 'Menü ve öğeleri başarıyla silindi.']);
    }
}
