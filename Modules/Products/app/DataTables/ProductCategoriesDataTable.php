<?php

namespace Modules\Products\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductsLanguageService;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $filters = request()->validate(['lang' => ['nullable', 'string']]);
        $language = app(ProductsLanguageService::class)->forAdmin($filters['lang'] ?? null);

        return DataTables::eloquent(ProductCategory::query()->join('product_category_translations as selected_translation', function ($join) use ($language): void {
            $join->on('selected_translation.product_category_id', '=', 'product_categories.id')->where('selected_translation.lang_id', '=', $language?->id);
        })->select(['product_categories.id', 'product_categories.sort_order', 'product_categories.is_active', 'selected_translation.name as name']))
            ->editColumn('is_active', fn (ProductCategory $category): string => $category->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (ProductCategory $category): string => view('products::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
