<?php

namespace Modules\Store\Services;

use App\Services\Settings\SettingsService;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;

class StoreCartService
{
    private const SessionKey = 'store.cart.v1';

    public function __construct(private StoreFrontendService $frontend, private SettingsService $settings) {}

    /** @return list<array{product_id: int, variant_id: ?int, quantity: int}> */
    public function items(Store $session): array
    {
        return array_values($session->get(self::SessionKey, []));
    }

    public function add(Store $session, int $productId, ?int $variantId, int $quantity): void
    {
        $items = $this->items($session);
        $found = false;
        foreach ($items as &$item) {
            if ((int) $item['product_id'] === $productId && $item['variant_id'] === $variantId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        unset($item);
        if (! $found) {
            $items[] = ['product_id' => $productId, 'variant_id' => $variantId, 'quantity' => $quantity];
        }
        $this->quote($items);
        $session->put(self::SessionKey, $items);
    }

    public function update(Store $session, int $index, int $quantity): void
    {
        $items = $this->items($session);
        abort_unless(isset($items[$index]), 404);
        if ($quantity === 0) {
            unset($items[$index]);
        } else {
            $items[$index]['quantity'] = $quantity;
        }
        $items = array_values($items);
        if ($items !== []) {
            $this->quote($items);
        }
        $session->put(self::SessionKey, $items);
    }

    /** @param list<array{product_id: int, variant_id: ?int, quantity: int}> $items @return array<string, mixed> */
    public function quote(array $items, string $delivery = 'shipping', bool $lock = false): array
    {
        usort($items, fn (array $a, array $b): int => [$a['product_id'], $a['variant_id']] <=> [$b['product_id'], $b['variant_id']]);
        $lines = [];
        $currency = null;
        $net = 0;
        $tax = 0;
        $gross = 0;
        $rate = (int) $this->settings->get('store.tax_rate', 0);
        $included = (bool) $this->settings->get('store.tax_included', true);
        if ($delivery === 'pickup' && ! $this->settings->get('store.pickup_enabled', false)) {
            throw ValidationException::withMessages(['delivery_method' => 'Mağazadan teslim alma kapalı.']);
        }
        foreach ($items as $item) {
            [$product, $variant] = $this->frontend->sellable((int) $item['product_id'], $item['variant_id'], $lock);
            $quantity = (int) $item['quantity'];
            if ($quantity < 1 || $quantity > 100 || ($product->tracks_stock && ! $product->allow_backorders && ($variant ?? $product)->stock_quantity < $quantity)) {
                throw ValidationException::withMessages(['cart' => 'Ürün için yeterli stok yok veya adet geçersiz.']);
            }
            $currency ??= $product->currency;
            if ($currency !== $product->currency || $currency !== $this->settings->get('store.currency', 'TRY')) {
                throw ValidationException::withMessages(['cart' => 'Sepet para birimleri uyuşmuyor.']);
            }
            $unit = $variant?->price_minor ?? $product->price_minor;
            $amounts = StoreMoney::tax($unit * $quantity, $rate, $included);
            $net += $amounts['net'];
            $tax += $amounts['tax'];
            $gross += $amounts['gross'];
            $lines[] = compact('product', 'variant', 'quantity', 'unit', 'amounts');
        }
        $shipping = 0;
        $threshold = $this->settings->get('store.free_shipping_threshold', '');
        if ($lines !== [] && $delivery === 'shipping' && (! filled($threshold) || $gross < StoreMoney::parse((string) $threshold, $currency))) {
            $shipping = StoreMoney::parse((string) $this->settings->get('store.shipping_price', '0'), $currency);
        }
        $shippingAmounts = StoreMoney::tax($shipping, $rate, $included);
        $total = $gross + $shippingAmounts['gross'];
        $fingerprint = hash('sha256', json_encode(['lines' => array_map(fn (array $line): array => [$line['product']->id, $line['variant']?->id, $line['quantity'], $line['unit']], $lines), 'currency' => $currency, 'rate' => $rate, 'included' => $included, 'delivery' => $delivery, 'shipping' => $shipping, 'total' => $total], JSON_THROW_ON_ERROR));

        return compact('lines', 'currency', 'net', 'tax', 'gross', 'shippingAmounts', 'total', 'rate', 'included', 'delivery', 'fingerprint');
    }

    public function clear(Store $session): void
    {
        $session->forget(self::SessionKey);
    }
}
