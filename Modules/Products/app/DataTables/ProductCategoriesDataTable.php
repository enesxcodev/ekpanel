<?php

namespace Modules\Products\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Products\Models\ProductCategory;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(ProductCategory::query()->select(['id', 'name', 'sort_order', 'is_active']))
            ->editColumn('is_active', fn (ProductCategory $category): string => $category->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (ProductCategory $category): string => view('products::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
