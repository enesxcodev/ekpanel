<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreCartController;
use Modules\Store\Http\Controllers\StoreProductController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Store'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.store.')
    ->group(function (): void {
        Route::get('store', [StoreProductController::class, 'index'])->middleware('can:store.view')->name('index');
        Route::get('store/create', [StoreProductController::class, 'create'])->middleware('can:store.create')->name('create');
        Route::post('store', [StoreProductController::class, 'store'])->middleware('can:store.create')->name('store');
        Route::get('store/{product}/edit', [StoreProductController::class, 'edit'])->middleware('can:store.update')->name('edit');
        Route::put('store/{product}', [StoreProductController::class, 'update'])->middleware('can:store.update')->name('update');
        Route::delete('store/{product}', [StoreProductController::class, 'destroy'])->middleware('can:store.delete')->name('destroy');
    });

Route::middleware(['web', 'module.enabled:Store'])->prefix('magaza')->name('store.')->group(function (): void {
    Route::get('sepet', [StoreCartController::class, 'index'])->name('cart');
    Route::post('sepet', [StoreCartController::class, 'add'])->name('cart.add');
    Route::post('checkout', [StoreCartController::class, 'checkout'])->name('checkout');
    Route::view('siparis/{number}/tamamlandi', 'store::index')->name('order.complete');
});
