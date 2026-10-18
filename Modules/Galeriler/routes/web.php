<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Galeriler\Http\Controllers\GalerilerCategoryController;
use Modules\Galeriler\Http\Controllers\GalerilerItemController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Galeriler'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::post('galeriler/bulk', [GalerilerItemController::class, 'bulk'])->name('galeriler.bulk');
        Route::put('galeriler/reorder', [GalerilerItemController::class, 'reorder'])->name('galeriler.reorder');
        Route::resource('galeriler/categories', GalerilerCategoryController::class)->except('show')->parameters(['categories' => 'category'])->names('galeriler.categories');
        Route::resource('galeriler', GalerilerItemController::class)->except('show')->parameters(['galeriler' => 'item']);
    });
