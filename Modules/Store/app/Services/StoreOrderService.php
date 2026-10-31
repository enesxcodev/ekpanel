<?php

namespace Modules\Store\Services;

use App\Models\Admin\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Store\Models\StoreOrder;

class StoreOrderService
{
    public function __construct(private StoreCartService $cart, private StockService $stock) {}

    /** @param list<array{product_id: int, variant_id: ?int, quantity: int}> $items @param array<string, mixed> $customer */
    public function create(array $items, array $customer, ?User $user = null): StoreOrder
    {
        usort($items, fn (array $a, array $b): int => [$a['product_id'], $a['variant_id']] <=> [$b['product_id'], $b['variant_id']]);

        return Cache::lock('store.checkout.'.$customer['checkout_key'], 120)->block(10, fn (): StoreOrder => DB::transaction(function () use ($items, $customer, $user): StoreOrder {
            $languageCode = (string) request()->attributes->get('store_language', request()->query('lang', 'tr'));
            $hash = hash('sha256', json_encode([$items, $customer, $user?->id, $languageCode], JSON_THROW_ON_ERROR));
            $existing = StoreOrder::query()->where('checkout_key', $customer['checkout_key'])->lockForUpdate()->first();
            if ($existing) {
                if (! hash_equals($existing->checkout_hash, $hash)) {
                    throw ValidationException::withMessages(['cart' => 'Bu sipariş anahtarı farklı bilgilerle kullanılmış.']);
                }

                return $existing;
            }
            if ($items === []) {
                throw ValidationException::withMessages(['cart' => 'Sepetiniz boş.']);
            }
            usort($items, fn (array $a, array $b): int => [$a['product_id'], $a['variant_id']] <=> [$b['product_id'], $b['variant_id']]);
            $quote = $this->cart->quote($items, $customer['delivery_method'], true);
            if (! hash_equals($quote['fingerprint'], $customer['quote'])) {
                throw ValidationException::withMessages(['cart' => 'Sepet fiyatı veya ayarlar değişti. Güncel özeti yeniden onaylayın.']);
            }
            $order = StoreOrder::query()->create([
                'number' => 'STR-'.Str::upper((string) Str::ulid()), 'user_id' => $user?->id,
                'checkout_key' => $customer['checkout_key'], 'checkout_hash' => $hash, 'access_token' => Str::random(64),
                'customer_name' => $customer['customer_name'], 'customer_email' => $customer['customer_email'], 'customer_phone' => $customer['customer_phone'],
                'shipping_address' => $customer['delivery_method'] === 'pickup' ? null : $customer['address'],
                'billing_address' => array_filter($customer['billing_address'] ?? [], fn ($value): bool => filled($value)) !== [] ? $customer['billing_address'] : ($customer['address'] ?? null),
                'delivery_snapshot' => ['method' => $customer['delivery_method'], 'tax_rate' => $quote['rate'], 'tax_included' => $quote['included'], 'shipping' => $quote['shippingAmounts'], 'language' => $languageCode],
                'currency' => $quote['currency'], 'subtotal_minor' => $quote['net'], 'tax_minor' => $quote['tax'] + $quote['shippingAmounts']['tax'],
                'shipping_minor' => $quote['shippingAmounts']['gross'], 'total_minor' => $quote['total'],
                'reserved_until' => now()->addHours(24),
                'status_history' => [['status' => 'pending', 'at' => now()->toIso8601String()]],
            ]);
            foreach ($quote['lines'] as $line) {
                $product = $line['product'];
                $variant = $line['variant'];
                $this->stock->reserve($product, $variant, $line['quantity'], 'Sipariş stok ayırma', order: $order);
                $order->lines()->create([
                    'store_product_id' => $product->id, 'store_variant_id' => $variant?->id,
                    'product_title' => $product->title, 'variant_title' => $variant?->title, 'sku' => $variant?->sku ?? $product->sku,
                    'unit_price_minor' => $line['unit'], 'quantity' => $line['quantity'], 'line_total_minor' => $line['amounts']['gross'],
                    'snapshot' => ['language' => $languageCode, 'product_title' => $product->title, 'variant_title' => $variant?->title, 'currency' => $quote['currency'], 'tax_rate' => $quote['rate'], 'tax_included' => $quote['included'], 'amounts' => $line['amounts'], 'options' => $variant?->option_values, 'tracks_stock' => $product->tracks_stock, 'weight_grams' => $product->weight_grams, 'dimensions_cm' => [$product->width_cm, $product->height_cm, $product->length_cm], 'preparation_days' => $product->preparation_days, 'delivery_description' => $product->delivery_description],
                ]);
            }

            return $order;
        }, 3));
    }

    /** @param array<string, mixed> $data */
    public function transition(StoreOrder $order, array $data, ?Admin $actor = null): StoreOrder
    {
        return DB::transaction(function () use ($order, $data, $actor): StoreOrder {
            $order = StoreOrder::query()->lockForUpdate()->findOrFail($order->id);
            $status = $data['status'] ?? $order->status;
            $payment = $data['payment_status'] ?? $order->payment_status;
            $pickup = ($order->delivery_snapshot['method'] ?? 'shipping') === 'pickup';
            $allowed = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['preparing', 'cancelled'], 'preparing' => [$pickup ? 'ready' : 'shipped', 'cancelled'], 'ready' => ['completed', 'cancelled'], 'shipped' => ['completed'], 'completed' => [], 'cancelled' => []];
            if ($status !== $order->status && ! in_array($status, $allowed[$order->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Bu sipariş durum geçişine izin verilmiyor.']);
            }
            if ($payment !== $order->payment_status) {
                abort_unless($actor?->can('store.orders.payment'), 403);
                $payments = ['unpaid' => $order->status === 'cancelled' ? [] : ['paid'], 'paid' => $status === 'cancelled' ? ['refunded'] : [], 'refunded' => []];
                if (! in_array($payment, $payments[$order->payment_status] ?? [], true) || ! filled($data['note'] ?? null)) {
                    throw ValidationException::withMessages(['payment_status' => 'Ödeme geçişi geçersiz veya referans/açıklama eksik.']);
                }
            }
            if ($status === 'completed' && $payment !== 'paid') {
                throw ValidationException::withMessages(['status' => 'Tamamlamak için ödeme alınmış olmalı.']);
            }
            if ($status === 'cancelled') {
                $this->stock->release($order);
            }
            $history = $order->status_history ?? [];
            $history[] = ['status' => $status, 'payment_status' => $payment, 'note' => $data['note'] ?? null, 'admin_id' => $actor?->id, 'at' => now()->toIso8601String()];
            $order->fill(['status' => $status, 'payment_status' => $payment, 'status_history' => $history]);
            if (array_key_exists('tracking_number', $data)) {
                $order->tracking_number = $data['tracking_number'];
            }
            if ($status === 'shipped' && ! $order->shipped_at) {
                $order->shipped_at = now();
            }
            $order->save();

            return $order;
        });
    }

    public function expire(): int
    {
        $count = 0;
        StoreOrder::query()->where('payment_status', 'unpaid')->whereNotIn('status', ['cancelled', 'shipped', 'completed'])->where('reserved_until', '<=', now())->pluck('id')->each(function (int $id) use (&$count): void {
            DB::transaction(function () use ($id, &$count): void {
                $order = StoreOrder::query()->lockForUpdate()->find($id);
                if ($order && $order->payment_status === 'unpaid' && $order->status !== 'cancelled' && $order->reserved_until?->lte(now())) {
                    $this->transition($order, ['status' => 'cancelled', 'note' => 'Ödeme rezervasyon süresi doldu.']);
                    $count++;
                }
            });
        });

        return $count;
    }
}
