<?php

namespace Modules\Products\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Products\Models\Product;
use Yajra\DataTables\Facades\DataTables;

class ProductsDataTable
{
    public function handle(): JsonResponse
    {
        $filters = request()->validate(['category_id' => ['nullable', 'integer'], 'is_active' => ['nullable', 'boolean']]);
        $query = Product::query()->with(['category:id,name', 'imageMedia'])->select(['products.id', 'products.title', 'products.product_category_id', 'products.image', 'products.image_media_id', 'products.is_active', 'products.slug', 'products.sort_order']);
        if (isset($filters['category_id'])) {
            $query->where('product_category_id', $filters['category_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn (Product $product): string => $product->category?->name ?? 'Kategorisiz')
            ->addColumn('visual', fn (Product $product): string => view('products::partials.visual', compact('product'))->render())
            ->editColumn('is_active', fn (Product $product): string => $product->is_active ? 'Yayında' : 'Taslak')
            ->addColumn('actions', fn (Product $product): string => view('products::partials.actions', compact('product'))->render())
            ->rawColumns(['visual', 'actions'])
            ->toJson();
    }
}
