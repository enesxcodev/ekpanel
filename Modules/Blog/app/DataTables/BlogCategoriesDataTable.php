<?php

namespace Modules\Blog\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Blog\Models\BlogCategory;
use Yajra\DataTables\Facades\DataTables;

class BlogCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(BlogCategory::query()->select(['id', 'parent_id', 'name', 'slug', 'sort_order'])->with('parent')->withCount(['children', 'contents']))
            ->addColumn('parent_name', fn (BlogCategory $category): string => $category->parent?->name ?? '—')
            ->addColumn('actions', fn (BlogCategory $category): string => view('blog::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])->toJson();
    }
}
