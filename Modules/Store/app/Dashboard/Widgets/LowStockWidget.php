<?php

namespace Modules\Store\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Store\Models\StoreProduct;

class LowStockWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'store.stock.low',
            'title' => 'Düşük stoklu ürünler',
            'icon' => 'briefcase',
            'permissions' => ['store.stock.view'],
            'default' => false,
            'order' => 130,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => StoreProduct::query()->where('is_archived', false)->where('tracks_stock', true)
                ->where(function ($query): void {
                    $query->where(fn ($simple) => $simple->where('has_variants', false)->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
                        ->orWhere(fn ($variable) => $variable->where('has_variants', true)
                            ->whereHas('variants', fn ($variants) => $variants->where('is_active', true)->where('is_archived', false)
                                ->whereColumn('store_variants.stock_quantity', '<=', 'store_products.low_stock_threshold')));
                })->count(),
            'description' => 'Stok takibi açık, arşiv dışı ürünler',
            'url' => route('admin.store.stock.index', absolute: false),
        ];
    }
}
