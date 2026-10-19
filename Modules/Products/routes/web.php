<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\ProductCategoryController;
use Modules\Products\Http\Controllers\ProductController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Products'])
    ->prefix((trim((string) config('app.route_files.admin_prefix')) ?: 'admin').'/products')
    ->name('admin.products.')
    ->group(function (): void {
        Route::get('categories/data', [ProductCategoryController::class, 'data'])->name('categories.data');
        Route::resource('categories', ProductCategoryController::class)->except('show');
        Route::get('data', [ProductController::class, 'data'])->name('data');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
