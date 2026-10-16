<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\ServiceCategoryController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Services'])
    ->prefix((trim((string) config('app.route_files.admin_prefix')) ?: 'admin').'/services')
    ->name('admin.services.')
    ->group(function (): void {
        Route::get('categories/data', [ServiceCategoryController::class, 'data'])->name('categories.data');
        Route::resource('categories', ServiceCategoryController::class)->except('show');
    });
