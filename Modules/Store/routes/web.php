<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\PublicStoreController;
use Modules\Store\Http\Controllers\StoreCartController;
use Modules\Store\Http\Controllers\StoreCategoryController;
use Modules\Store\Http\Controllers\StoreOrderController;
use Modules\Store\Http\Controllers\StoreProductController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Store'])->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')->name('admin.store.')->group(function (): void {
    Route::get('store', [StoreProductController::class, 'index'])->middleware('can:store.view')->name('index');
    Route::get('store/data', [StoreProductController::class, 'data'])->middleware('can:store.view')->name('data');
    Route::get('store/catalog', [StoreProductController::class, 'catalog'])->middleware('can:store.create')->name('catalog');
    Route::get('store/create', [StoreProductController::class, 'create'])->middleware('can:store.create')->name('create');
    Route::post('store', [StoreProductController::class, 'store'])->middleware('can:store.create')->name('store');
    Route::get('store/{product}/edit', [StoreProductController::class, 'edit'])->middleware('can:store.update')->name('edit');
    Route::put('store/{product}', [StoreProductController::class, 'update'])->middleware('can:store.update')->name('update');
    Route::delete('store/{product}', [StoreProductController::class, 'destroy'])->middleware('can:store.delete')->name('destroy');
    Route::get('store-categories/data', [StoreCategoryController::class, 'data'])->middleware('can:store.categories.view')->name('categories.data');
    Route::get('store-categories', [StoreCategoryController::class, 'index'])->middleware('can:store.categories.view')->name('categories.index');
    Route::get('store-categories/create', [StoreCategoryController::class, 'create'])->middleware('can:store.categories.create')->name('categories.create');
    Route::post('store-categories', [StoreCategoryController::class, 'store'])->middleware('can:store.categories.create')->name('categories.store');
    Route::get('store-categories/{category}/edit', [StoreCategoryController::class, 'edit'])->middleware('can:store.categories.update')->name('categories.edit');
    Route::put('store-categories/{category}', [StoreCategoryController::class, 'update'])->middleware('can:store.categories.update')->name('categories.update');
    Route::delete('store-categories/{category}', [StoreCategoryController::class, 'destroy'])->middleware('can:store.categories.delete')->name('categories.destroy');
    Route::get('store-orders', [StoreOrderController::class, 'index'])->middleware('can:store.orders.view')->name('orders.index');
    Route::get('store-orders/data', [StoreOrderController::class, 'data'])->middleware('can:store.orders.view')->name('orders.data');
    Route::get('store-orders/{order}', [StoreOrderController::class, 'show'])->middleware('can:store.orders.view')->name('orders.show');
    Route::put('store-orders/{order}', [StoreOrderController::class, 'update'])->middleware('can:store.orders.update')->name('orders.update');
    Route::get('store-stock', [StoreOrderController::class, 'stock'])->middleware('can:store.stock.view')->name('stock.index');
    Route::get('store-stock/data', [StoreOrderController::class, 'stockData'])->middleware('can:store.stock.view')->name('stock.data');
});
Route::middleware('module.enabled:Store')->prefix('magaza')->name('store.')->group(function (): void {
    Route::get('/', [PublicStoreController::class, 'index'])->name('index');
    Route::get('urun/{slug}', [PublicStoreController::class, 'show'])->name('show');
    Route::get('sepet', [StoreCartController::class, 'index'])->name('cart');
    Route::post('sepet', [StoreCartController::class, 'add'])->name('cart.add');
    Route::put('sepet/{index}', [StoreCartController::class, 'update'])->whereNumber('index')->name('cart.update');
    Route::get('checkout', [StoreCartController::class, 'form'])->name('checkout.form');
    Route::post('checkout', [StoreCartController::class, 'checkout'])->middleware('throttle:20,1')->name('checkout');
    Route::get('siparis/{number}/tamamlandi/{token}', [StoreCartController::class, 'complete'])->name('order.complete');
    Route::get('siparislerim', [StoreCartController::class, 'account'])->name('account');
    Route::get('siparislerim/{number}',[StoreCartController::class, 'accountOrder'])->name('account.order');
});
