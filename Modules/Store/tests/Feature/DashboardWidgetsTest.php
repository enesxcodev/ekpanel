<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Store\Dashboard\Widgets\LowStockWidget;
use Modules\Store\Dashboard\Widgets\PendingOrdersWidget;
use Modules\Store\Models\StoreOrder;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;

uses(LazilyRefreshDatabase::class);

test('store.orders.pending widget reports the domain count and excludes ineligible records', function () {
    StoreOrder::factory()->create(['status' => 'pending', 'payment_status' => 'unpaid']);
    StoreOrder::factory()->create(['status' => 'pending', 'payment_status' => 'paid']);
    StoreOrder::factory()->create(['status' => 'confirmed', 'payment_status' => 'unpaid']);
    StoreOrder::factory()->create(['status' => 'completed', 'payment_status' => 'paid']);

    $result = (new PendingOrdersWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('store.stock.low widget reports the domain count and excludes ineligible records', function () {
    StoreProduct::factory()->create(['stock_quantity' => 2, 'low_stock_threshold' => 2]);
    StoreProduct::factory()->create(['stock_quantity' => 3, 'low_stock_threshold' => 2]);
    StoreProduct::factory()->create(['stock_quantity' => 0, 'tracks_stock' => false]);
    StoreProduct::factory()->create(['stock_quantity' => 0, 'is_archived' => true]);
    $variable = StoreProduct::factory()->create(['has_variants' => true, 'stock_quantity' => 99, 'low_stock_threshold' => 2]);
    StoreVariant::factory()->for($variable, 'product')->count(2)->create(['stock_quantity' => 2]);
    $healthy = StoreProduct::factory()->create(['has_variants' => true, 'stock_quantity' => 0, 'low_stock_threshold' => 2]);
    StoreVariant::factory()->for($healthy, 'product')->create(['stock_quantity' => 3]);
    StoreVariant::factory()->for($healthy, 'product')->create(['stock_quantity' => 0, 'is_archived' => true]);
    StoreVariant::factory()->for($healthy, 'product')->create(['stock_quantity' => 0, 'is_active' => false]);

    $result = (new LowStockWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
