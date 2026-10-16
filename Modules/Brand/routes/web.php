<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Brand\Http\Controllers\BrandController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Brand'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.brand.')
    ->group(function (): void {
        Route::get('brands/data', [BrandController::class, 'data'])->name('data');
        Route::resource('brands', BrandController::class)->parameters(['brands' => 'brand'])->except('show')->names([
            'index' => 'index', 'create' => 'create', 'store' => 'store', 'edit' => 'edit', 'update' => 'update', 'destroy' => 'destroy',
        ]);
    });
