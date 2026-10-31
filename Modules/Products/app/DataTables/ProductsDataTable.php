<?php

namespace Modules\Products\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Products\Models\Product;
use Modules\Products\Services\ProductsLanguageService;
use Yajra\DataTables\Facades\DataTables;

class ProductsDataTable
{
    public function handle(): JsonResponse
    {
        $filters = request()->validate(['category_id' => ['nullable', 'integer'], 'is_active' => ['nullable', 'boolean'], 'lang' => ['nullable', 'string']]);
        $language = app(ProductsLanguageService::class)->forAdmin($filters['lang'] ?? null);
        $query = Product::query()->leftJoin('product_translations as selected_translation', function ($join) use ($language): void {
            $join->on('selected_translation.product_id', '=', 'products.id')->where('selected_translation.lang_id', '=', $language?->id);
        })->leftJoin('product_category_translations as selected_category_translation', function ($join) use ($language): void {
            $join->on('selected_category_translation.product_category_id', '=', 'products.product_category_id')->where('selected_category_translation.lang_id', '=', $language?->id);
        })->with(['category.translations' => fn ($translation) => $translation->where('lang_id', $language?->id), 'imageMedia'])->whereNotNull('selected_translation.id')->select(['products.id', 'products.product_category_id', 'products.image', 'products.image_media_id', 'products.is_active', 'products.sort_order', 'selected_translation.title as title', 'selected_translation.slug as slug', 'selected_category_translation.name as category_name']);
        if (isset($filters['category_id'])) {
            $query->where('products.product_category_id', $filters['category_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return DataTables::eloquent($query)
            ->editColumn('category_name', fn (Product $product): string => $product->product_category_id === null ? 'Kategorisiz' : ($product->category?->name ?? 'Kategori çevirisi eksik'))
            ->addColumn('visual', fn (Product $product): string => view('products::partials.visual', compact('product'))->render())
            ->editColumn('is_active', fn (Product $product): string => $product->is_active ? 'Yayında' : 'Taslak')
            ->addColumn('actions', fn (Product $product): string => view('products::partials.actions', compact('product'))->render())
            ->rawColumns(['visual', 'actions'])
            ->toJson();
    }
}
