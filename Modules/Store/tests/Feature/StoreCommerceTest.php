<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Products\Models\Product;
use Modules\Store\Models\StoreOrder;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Services\StoreCartService;
use Modules\Store\Services\StoreOrderService;

uses(LazilyRefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
});

require_once __DIR__.'/../Fixtures/store.php';

test('creates independent products with editable slug money media delivery and SEO', function () {
    storeStatuses(['Products' => false]);
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $image = storeImage('kapak.png');
    $second = storeImage('galeri.png');
    $this->actingAs($admin, 'admin')->post(route('admin.store.store'), storePayload(['slug' => 'ozel-kapi', 'image_media_id' => $image->id, 'image_media_ids' => [$second->id, $image->id], 'meta_title' => 'Özel SEO', 'weight_grams' => 2500, 'content' => '<p>Detay</p><script>alert(1)</script>', 'id' => 999]))->assertRedirectToRoute('admin.store.index');
    $product = StoreProduct::query()->sole();
    expect($product->price_minor)->toBe(12050);
    expect($product->images->modelKeys())->toBe([$second->id, $image->id]);
    expect($product->content)->not->toContain('<script>');
    $this->assertDatabaseHas('store_products', ['slug' => 'ozel-kapi', 'meta_title' => 'Özel SEO', 'weight_grams' => 2500]);
    $this->assertDatabaseHas('media_usages', ['owner_type' => StoreProduct::class, 'owner_id' => $product->id, 'field' => 'image']);
    $this->get(route('admin.store.edit', $product))->assertOk()->assertSee('SEO ayarları')->assertSee('Teslimat')->assertSee('data-variant-editor', false);
    $this->get(route('store.show', $product->slug))->assertOk()->assertSee('Özel SEO')->assertDontSee('<script>alert(1)', false);
    $this->assertDatabaseCount('notifications', 0);
});
test('imports catalogue media and preserves Store content after the source is deleted', function () {
    storeStatuses();
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $image = storeImage('source.png');
    $source = Product::factory()->create(['image_media_id' => $image->id, 'image' => $image->path]);
    $source->images()->attach($image, ['sort_order' => 0]);
    $this->actingAs($admin, 'admin')->getJson(route('admin.store.catalog', ['q' => $source->title]))->assertOk()->assertJsonPath('items.0.image_media_id', $image->id)->assertJsonPath('items.0.images.0.id', $image->id);
    $this->post(route('admin.store.store'), storePayload(['source_product_id' => $source->id, 'image_media_id' => $image->id, 'image_media_ids' => [$image->id]]))->assertRedirectToRoute('admin.store.index');
    $product = StoreProduct::query()->sole();
    $source->delete();
    storeStatuses(['Products' => false]);
    expect($product->fresh()->image_url)->not->toBeNull();
    expect($product->fresh()->images->modelKeys())->toBe([$image->id]);
    $this->getJson(route('admin.store.catalog'))->assertForbidden();
    $this->assertDatabaseHas('media_usages', ['owner_type' => StoreProduct::class, 'owner_id' => $product->id, 'field' => 'gallery.'.$image->id]);
});
test('creates and edits variants on the product form and rejects a foreign variant', function () {
    storeStatuses();
    $admin = Admin::factory()->superAdmin()->create();
    $payload = storePayload(['has_variants' => 1, 'option_groups' => [['name' => 'Renk', 'values' => 'Siyah, Beyaz']], 'variants' => [['sku' => 'BLACK', 'option_values' => ['Renk' => 'Siyah'], 'price' => '130.00', 'stock_quantity' => 3, 'is_active' => 1]]]);
    $this->actingAs($admin, 'admin')->post(route('admin.store.store'), $payload)->assertRedirectToRoute('admin.store.index');
    $product = StoreProduct::query()->sole();
    $variant = $product->variants->sole();
    $payload['variants'][0]['id'] = $variant->id;
    $payload['variants'][0]['price'] = '140.25';
    $this->put(route('admin.store.update', $product), $payload)->assertRedirectToRoute('admin.store.index');
    expect($variant->fresh()->price_minor)->toBe(14025);
    $this->assertDatabaseCount('store_variants', 1);
    $this->post(route('store.cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertSessionHasErrors('variant_id');
    $other = StoreProduct::factory()->create(['has_variants' => true]);
    $this->post(route('store.cart.add'), ['product_id' => $other->id, 'variant_id' => $variant->id, 'quantity' => 1])->assertSessionHasErrors('variant_id');
});
test('rejects duplicate variant combinations and invalid comparison price atomically', function () {
    storeStatuses();
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.store.store'), storePayload(['compare_at_price' => '10']))->assertSessionHasErrors('compare_at_price');
    $row = ['sku' => 'A', 'option_values' => ['Renk' => 'Siyah'], 'stock_quantity' => 1, 'is_active' => 1];
    $this->post(route('admin.store.store'), storePayload(['has_variants' => 1, 'option_groups' => [['name' => 'Renk', 'values' => 'Siyah']], 'variants' => [$row, [...$row, 'sku' => 'B']]]))->assertSessionHasErrors('variants');
    $this->assertDatabaseCount('store_products', 0);
    $this->assertDatabaseCount('store_variants', 0);
});
test('calculates included and excluded tax using integer line rounding', function (bool $included, int $total, int $tax) {
    storeStatuses();
    storeSettings(['store.tax_included' => $included, 'store.tax_rate' => 20]);
    $product = StoreProduct::factory()->create(['price_minor' => 1000]);
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 3]];
    $order = app(StoreOrderService::class)->create($items, storeCustomer($items));
    expect($order->total_minor)->toBe($total);
    expect($order->tax_minor)->toBe($tax);
})->with([[true, 3000, 500], [false, 3600, 600]]);
test('checkout repeats return one order and cancellation releases stock only once', function () {
    storeStatuses();
    storeSettings(['store.guest_checkout_enabled' => true]);
    $this->freezeTime();
    $product = StoreProduct::factory()->create(['stock_quantity' => 1]);
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $customer = storeCustomer($items);
    $orders = app(StoreOrderService::class);
    $order = $orders->create($items, $customer);
    $repeated = $orders->create($items, $customer);
    expect($repeated->id)->toBe($order->id);
    expect($product->fresh()->stock_quantity)->toBe(0);
    $orders->transition($order, ['status' => 'cancelled']);
    $orders->transition($order, ['status' => 'cancelled']);
    expect($product->fresh()->stock_quantity)->toBe(1);
    $this->assertDatabaseCount('store_orders', 1);
    $this->assertDatabaseCount('store_stock_movements', 2);
});
test('guest checkout creates an unpaid order and protects its result token', function () {
    storeStatuses();
    storeSettings(['store.guest_checkout_enabled' => true]);
    $product = StoreProduct::factory()->create();
    $this->post(route('store.cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirectToRoute('store.cart');
    $this->get(route('store.checkout.form'))->assertOk()->assertSee('Özeti onayla');
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $customer = storeCustomer($items, ['checkout_key' => session('store.checkout_key')]);
    $this->post(route('store.checkout'), $customer)->assertRedirect();
    $this->post(route('store.checkout'), $customer)->assertRedirect();
    $this->post(route('store.checkout'), [...$customer, 'customer_name' => 'Değişmiş müşteri'])->assertSessionHasErrors('cart');
    $this->assertDatabaseCount('store_orders', 1);
    $order = StoreOrder::query()->sole();
    expect($order->payment_status)->toBe('unpaid');
    $this->get(route('store.order.complete', ['number' => $order->number, 'token' => $order->access_token]))->assertOk()->assertSee($order->number);
    $this->get(route('store.order.complete', ['number' => $order->number, 'token' => str_repeat('x', 64)]))->assertNotFound();
    $this->get(route('store.account.order', $order->number))->assertForbidden();
    $this->post(route('store.cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirectToRoute('store.cart');
    $this->get(route('store.checkout.form'))->assertOk();
    expect(session('store.checkout_key'))->not->toBe($customer['checkout_key']);
    $nextCustomer = storeCustomer($items, ['checkout_key' => session('store.checkout_key')]);
    $this->post(route('store.checkout'), $nextCustomer)->assertRedirect();
    $this->assertDatabaseCount('store_orders', 2);
});
test('requires a web account when guests are disabled and does not bypass missing Users', function () {
    storeStatuses(['Users' => false]);
    storeSettings();
    $this->get(route('store.checkout.form'))->assertOk()->assertSee('Users modülünü etkinleştirmeli');
    $this->post(route('store.checkout'), [])->assertForbidden();
    $this->assertDatabaseCount('store_orders', 0);
});
test('expired reservations restore inventory and paid orders remain reserved', function () {
    storeStatuses();
    storeSettings();
    $this->freezeTime();
    $product = StoreProduct::factory()->create(['stock_quantity' => 3]);
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $orders = app(StoreOrderService::class);
    $first = $orders->create($items, storeCustomer($items));
    $second = $orders->create($items, storeCustomer($items));
    $admin = Admin::factory()->superAdmin()->create();
    $orders->transition($second, ['payment_status' => 'paid', 'note' => 'Banka referansı'], $admin);
    $this->travel(25)->hours();
    expect($orders->expire())->toBe(1);
    expect($first->fresh()->status)->toBe('cancelled');
    expect($second->fresh()->payment_status)->toBe('paid');
    expect($product->fresh()->stock_quantity)->toBe(2);
});
test('rejects price changes and insufficient final inventory without creating orders', function () {
    storeStatuses();
    storeSettings();
    $product = StoreProduct::factory()->create(['stock_quantity' => 1]);
    $items = [['product_id' => $product->id, 'variant_id' => null, 'quantity' => 1]];
    $customer = storeCustomer($items);
    $product->update(['price_minor' => 15000]);
    expect(fn () => app(StoreOrderService::class)->create($items, $customer))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('store_orders', 0);
    $product->update(['stock_quantity' => 0]);
    expect(fn () => app(StoreCartService::class)->quote($items))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('store_stock_movements', 0);
});
test('admin list endpoints render common tables and unauthorized writes are forbidden', function () {
    storeStatuses();
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');
    foreach (['admin.store.index', 'admin.store.categories.index', 'admin.store.orders.index', 'admin.store.stock.index'] as $route) {
        $this->get(route($route))->assertOk()->assertSee('data-table', false);
    }
    foreach (['admin.store.data', 'admin.store.categories.data', 'admin.store.orders.data', 'admin.store.stock.data'] as $route) {
        $this->getJson(route($route))->assertOk();
    }
    $other = Admin::factory()->create();
    $this->actingAs($other, 'admin')->post(route('admin.store.store'), storePayload())->assertForbidden();
    $this->assertDatabaseCount('store_products', 0);
});
