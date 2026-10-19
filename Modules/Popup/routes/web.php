<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Popup\Http\Controllers\PopupController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Popup'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.popup.')
    ->group(function (): void {
        Route::get('popup/data', [PopupController::class, 'data'])->name('data');
        Route::resource('popup', PopupController::class)->except('show')->names([
            'index' => 'index', 'create' => 'create', 'store' => 'store',
            'edit' => 'edit', 'update' => 'update', 'destroy' => 'destroy',
        ]);
    });
