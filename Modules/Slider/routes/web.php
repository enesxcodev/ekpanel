<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Slider\Http\Controllers\SliderController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Slider'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.slider.')
    ->group(function (): void {
        Route::get('slider/data', [SliderController::class, 'data'])->name('data');
        Route::resource('slider', SliderController::class)->except('show')->names([
            'index' => 'index', 'create' => 'create', 'store' => 'store',
            'edit' => 'edit', 'update' => 'update', 'destroy' => 'destroy',
        ]);
    });
