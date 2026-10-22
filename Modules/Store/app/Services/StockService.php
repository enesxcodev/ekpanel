<?php

namespace Modules\Store\Services;

use App\Models\Admin\Admin;
use Illuminate\Validation\ValidationException;
use Modules\Store\Models\StockMovement;
use Modules\Store\Models\StoreOrder;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;

class StockService
{
    public function reserve(StoreProduct $product, ?StoreVariant $variant, int $quantity, string $reason, ?Admin $actor = null, ?StoreOrder $order = null): void
    {
        $stockable = $variant ?? $product;
        if (! $product->tracks_stock) {
            return;
        }
        if (! $product->allow_backorders && $stockable->stock_quantity < $quantity) {
            throw ValidationException::withMessages(['cart' => 'Ürün için yeterli stok yok.']);
        }
        $stockable->decrement('stock_quantity', $quantity);
        StockMovement::query()->create([
            'store_product_id' => $product->id, 'store_variant_id' => $variant?->id,
            'quantity' => -$quantity, 'resulting_quantity' => $stockable->fresh()->stock_quantity,
            'reason' => $reason, 'admin_id' => $actor?->id,
            'reference_type' => $order?->getMorphClass(), 'reference_id' => $order?->id,
        ]);
    }

    public function release(StoreOrder $order): void
    {
        if ($order->stock_released_at) {
            return;
        }
        foreach ($order->lines as $line) {
            if (! ($line->snapshot['tracks_stock'] ?? false)) {
                continue;
            }
            $product = StoreProduct::query()->lockForUpdate()->find($line->store_product_id);
            $variant = $line->store_variant_id ? StoreVariant::query()->lockForUpdate()->find($line->store_variant_id) : null;
            $stockable = $line->store_variant_id ? $variant : $product;
            if (! $product || ! $stockable) {
                continue;
            }
            $stockable->increment('stock_quantity', $line->quantity);
            StockMovement::query()->create(['store_product_id' => $product->id, 'store_variant_id' => $variant?->id, 'quantity' => $line->quantity, 'resulting_quantity' => $stockable->fresh()->stock_quantity, 'reason' => 'Sipariş stok iadesi', 'reference_type' => $order->getMorphClass(), 'reference_id' => $order->id]);
        }
        $order->stock_released_at = now();
    }
}
