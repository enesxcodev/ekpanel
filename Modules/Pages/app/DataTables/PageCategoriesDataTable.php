<?php

namespace Modules\Pages\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Pages\Models\PageCategory;
use Yajra\DataTables\Facades\DataTables;

class PageCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(PageCategory::query()->with('parent')->select(['id', 'parent_id', 'name', 'slug', 'sort_order']))
            ->addColumn('parent_name', fn (PageCategory $category): string => $category->parent?->name ?? '—')
            ->addColumn('actions', fn (PageCategory $category): string => view('pages::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
