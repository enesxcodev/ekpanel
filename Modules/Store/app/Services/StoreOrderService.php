<?php

namespace Modules\Store\Services;

use App\Models\User;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Store\Models\StoreOrder;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;

class StoreOrderService
{
    public function __construct(private SettingsService $settings, private StockService $stock) {}

    /** @param list<array{product_id: int, variant_id: ?int, quantity: int}> $cart @param array{name: string, email: string, address: array<string, string>} $customer */
    public function create(array $cart, array $customer, ?User $user = null): StoreOrder
    {
        if ($cart === []) {
            throw new \DomainException('Sepetiniz boş.');
        }

        return DB::transaction(function () use ($cart, $customer, $user): StoreOrder {
            $currency = null;
            $subtotal = 0;
            $lines = [];
            foreach ($cart as $item) {
                $product = StoreProduct::query()->lockForUpdate()->findOrFail($item['product_id']);
                $variant = $item['variant_id'] === null ? null : StoreVariant::query()->whereKey($item['variant_id'])->where('store_product_id', $product->id)->lockForUpdate()->firstOrFail();
                if (! $product->is_active || ($variant !== null && ! $variant->is_active)) {
                    throw new \DomainException('Sepetteki bir ürün artık satışta değil.');
                }
                $currency ??= $product->currency;
                if ($currency !== $product->currency) {
                    throw new \DomainException('Sepette farklı para birimleri birlikte kullanılamaz.');
                }
                $unitPrice = $variant?->price_minor ?? $product->price_minor;
                $lineTotal = $unitPrice * $item['quantity'];
                $this->stock->reserve($product, $variant, $item['quantity'], 'order');
                $subtotal += $lineTotal;
                $lines[] = compact('product', 'variant', 'unitPrice', 'lineTotal') + ['quantity' => $item['quantity']];
            }
            $taxRate = (float) $this->settings->get('store.tax_rate', 0);
            $taxIncluded = (bool) $this->settings->get('store.tax_included', true);
            $tax = $taxIncluded ? (int) round($subtotal - ($subtotal / (1 + ($taxRate / 100)))) : (int) round($subtotal * ($taxRate / 100));
            $total = $taxIncluded ? $subtotal : $subtotal + $tax;
            $order = StoreOrder::query()->create([
                'number' => 'STR-'.Str::upper((string) Str::ulid()),
                'user_id' => $user?->id,
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'shipping_address' => $customer['address'],
                'currency' => $currency,
                'subtotal_minor' => $subtotal,
                'tax_minor' => $tax,
                'total_minor' => $total,
                'status_history' => [['status' => 'pending', 'at' => now()->toIso8601String()]],
            ]);
            foreach ($lines as $line) {
                $order->lines()->create([
                    'store_product_id' => $line['product']->id,
                    'store_variant_id' => $line['variant']?->id,
                    'product_title' => $line['product']->title,
                    'variant_title' => $line['variant']?->title,
                    'sku' => $line['variant']?->sku ?? $line['product']->sku,
                    'unit_price_minor' => $line['unitPrice'],
                    'quantity' => $line['quantity'],
                    'line_total_minor' => $line['lineTotal'],
                    'snapshot' => ['currency' => $currency, 'tax_rate' => $taxRate, 'tax_included' => $taxIncluded],
                ]);
            }

            return $order;
        });
    }
}
