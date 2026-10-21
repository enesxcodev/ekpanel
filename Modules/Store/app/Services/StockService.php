<?php

namespace Modules\Store\Services;

use App\Models\Admin\Admin;
use Modules\Store\Models\StockMovement;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;

class StockService
{
    public function reserve(StoreProduct $product, ?StoreVariant $variant, int $quantity, string $reason, ?Admin $actor = null): void
    {
        $stockable = $variant ?? $product;
        if (! $product->tracks_stock) {
            return;
        }
        if (! $product->allow_backorders && $stockable->stock_quantity < $quantity) {
            throw new \DomainException('Ürün için yeterli stok yok.');
        }
        $stockable->decrement('stock_quantity', $quantity);
        StockMovement::query()->create([
            'store_product_id' => $product->id,
            'store_variant_id' => $variant?->id,
            'quantity' => -$quantity,
            'resulting_quantity' => $stockable->fresh()->stock_quantity,
            'reason' => $reason,
            'admin_id' => $actor?->id,
        ]);
    }
}
