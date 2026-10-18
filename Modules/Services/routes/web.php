<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\ServiceCategoryController;
use Modules\Services\Http\Controllers\ServiceController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Services'])
    ->prefix((trim((string) config('app.route_files.admin_prefix')) ?: 'admin').'/services')
    ->name('admin.services.')
    ->group(function (): void {
        Route::get('categories/data', [ServiceCategoryController::class, 'data'])->name('categories.data');
        Route::resource('categories', ServiceCategoryController::class)->except('show');
        Route::get('data', [ServiceController::class, 'data'])->name('data');
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('create', [ServiceController::class, 'create'])->name('create');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('{service}', [ServiceController::class, 'update'])->name('update');
        Route::delete('{service}', [ServiceController::class, 'destroy'])->name('destroy');
    });
