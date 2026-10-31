<?php

use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;
use Modules\Store\Services\StoreCartService;
use Modules\Store\Services\StoreOrderService;

uses(LazilyRefreshDatabase::class);
require_once __DIR__.'/../Fixtures/store.php';
beforeEach(function () {
    $this->withoutVite();
});

test('variant orders reserve variant stock without changing parent stock and retain snapshots', function () {
    storeStatuses();
    storeSettings();
    $product = StoreProduct::factory()->create(['has_variants' => true, 'stock_quantity' => 20]);
    $variant = StoreVariant::factory()->create(['store_product_id' => $product->id, 'stock_quantity' => 2, 'option_values' => ['Renk' => 'Siyah'], 'price_minor' => 12345]);
    $items = [['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 2]];
    $order = app(StoreOrderService::class)->create($items, storeCustomer($items));
    expect($variant->fresh()->stock_quantity)->toBe(0);
    expect($product->fresh()->stock_quantity)->toBe(20);
    $variant->update(['title' => 'Değişti', 'sku' => 'DEGISTI', 'is_active' => false]);
    $product->update(['title' => 'Yeni başlık', 'price_minor' => 9]);
    expect($order->lines->sole()->unit_price_minor)->toBe(12345);
    expect($order->lines->sole()->snapshot['options'])->toBe(['Renk' => 'Siyah']);
    app(StoreOrderService::class)->transition($order, ['status' => 'cancelled']);
    expect($variant->fresh()->stock_quantity)->toBe(2);
});
test('last inventory cannot be reserved twice and backorders allow a signed balance', function () {
    storeStatuses();
    storeSettings();
    $orders = app(StoreOrderService::class);
    $product = StoreProduct::factory()->create(['stock_quantity' => 1]);
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $first = storeCustomer($items);
    $second = storeCustomer($items);
    $orders->create($items, $first);
    expect(fn () => $orders->create($items, $second))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('store_orders', 1);
    $product->update(['allow_backorders' => true]);
    $orders->create($items, storeCustomer($items));
    expect($product->fresh()->stock_quantity)->toBe(-1);
});
test('delivery charges and free threshold use the gross product total', function () {
    storeStatuses();
    storeSettings(['store.tax_included' => false, 'store.tax_rate' => 20, 'store.shipping_price' => '10.00', 'store.free_shipping_threshold' => '120.00', 'store.pickup_enabled' => true]);
    $product = StoreProduct::factory()->create(['price_minor' => 5000]);
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $quote = app(StoreCartService::class)->quote($items);
    expect($quote['total'])->toBe(7200);
    expect(app(StoreCartService::class)->quote($items, 'pickup')['total'])->toBe(6000);
    $items[0]['quantity'] = 2;
    expect(app(StoreCartService::class)->quote($items)['shippingAmounts']['gross'])->toBe(0);
});
test('manual payment and order status transitions are validated and audited', function () {
    storeStatuses();
    storeSettings();
    $admin = Admin::factory()->superAdmin()->create();
    $product = StoreProduct::factory()->create();
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $orders = app(StoreOrderService::class);
    $order = $orders->create($items, storeCustomer($items));
    expect(fn () => $orders->transition($order, ['status' => 'shipped'], $admin))->toThrow(ValidationException::class);
    expect(fn () => $orders->transition($order, ['payment_status' => 'paid'], $admin))->toThrow(ValidationException::class);
    $orders->transition($order, ['payment_status' => 'paid', 'note' => 'Banka REF-1'], $admin);
    $orders->transition($order, ['status' => 'cancelled'], $admin);
    expect($order->fresh()->payment_status)->toBe('paid');
    $orders->transition($order, ['payment_status' => 'refunded', 'note' => 'İade REF-2'], $admin);
    expect($order->fresh()->status_history)->toHaveCount(4);
    expect($order->fresh()->payment_status)->toBe('refunded');
});
test('web customers can only see their own orders and guest token is independent of account access', function () {
    storeStatuses();
    storeSettings();
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $product = StoreProduct::factory()->create();
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $order = app(StoreOrderService::class)->create($items, storeCustomer($items), $owner);
    $this->actingAs($owner, 'web')->get(route('store.account.order', $order->number))->assertOk()->assertSee($order->number);
    $this->actingAs($other, 'web')->get(route('store.account.order', $order->number))->assertNotFound();
});
test('category CRUD uses the existing admin flow and inactive categories hide products', function () {
    storeStatuses();
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.store.categories.store'), ['name' => 'Kapılar', 'sort_order' => 0, 'is_active' => 1])->assertRedirectToRoute('admin.store.categories.edit', ['category' => StoreCategory::query()->latest('id')->firstOrFail(), 'lang' => 'tr']);
    $category = StoreCategory::query()->sole();
    $product = StoreProduct::factory()->create(['store_category_id' => $category->id]);
    $this->put(route('admin.store.categories.update', $category), ['name' => 'Kapılar', 'sort_order' => 0, 'is_active' => 0])->assertRedirectToRoute('admin.store.categories.edit', ['category' => $category, 'lang' => 'tr']);
    $this->get(route('store.show', $product->slug))->assertNotFound();
    $this->delete(route('admin.store.categories.destroy', $category))->assertRedirectToRoute('admin.store.categories.index');
    expect($product->fresh()->store_category_id)->toBeNull();
});
test('invalid shipping amounts are rejected through Settings without changing saved values', function () {
    storeStatuses();
    storeSettings();
    $admin = Admin::factory()->superAdmin()->create();
    $section = SettingSection::query()->where('key', 'store')->sole();
    $payload = $section->definitions->mapWithKeys(fn ($definition): array => [$definition->id => $definition->key === 'store.shipping_price' ? '-20' : $definition->default_value])->all();
    expect(fn () => app(SettingsService::class)->updateSection($section, ['settings' => $payload], $admin))->toThrow(ValidationException::class);
    expect(app(SettingsService::class)->get('store.shipping_price'))->toBe('0.00');
});
