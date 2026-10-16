<?php

namespace Modules\Menus\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Menus\Models\Menu;
use Yajra\DataTables\Facades\DataTables;

class MenusDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Menu::query()->select('menus.*')->withCount('items'))
            ->editColumn('is_active', fn (Menu $menu): string => view('menus::partials.status', compact('menu'))->render())
            ->addColumn('actions', fn (Menu $menu): string => view('menus::partials.actions', compact('menu'))->render())
            ->rawColumns(['is_active', 'actions'])
            ->toJson();
    }
}
