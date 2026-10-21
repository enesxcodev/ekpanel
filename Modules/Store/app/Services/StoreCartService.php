<?php

namespace Modules\Store\Services;

use Illuminate\Session\Store;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;

class StoreCartService
{
    private const SessionKey = 'store.cart.v1';

    /** @return list<array{product_id: int, variant_id: ?int, quantity: int}> */
    public function items(Store $session): array
    {
        return array_values($session->get(self::SessionKey, []));
    }

    public function add(Store $session, StoreProduct $product, ?StoreVariant $variant, int $quantity): void
    {
        $items = $this->items($session);
        foreach ($items as $index => $item) {
            if ($item['product_id'] === $product->id && $item['variant_id'] === $variant?->id) {
                $items[$index]['quantity'] += $quantity;
                $session->put(self::SessionKey, $items);

                return;
            }
        }
        $items[] = ['product_id' => $product->id, 'variant_id' => $variant?->id, 'quantity' => $quantity];
        $session->put(self::SessionKey, $items);
    }

    public function clear(Store $session): void
    {
        $session->forget(self::SessionKey);
    }
}
