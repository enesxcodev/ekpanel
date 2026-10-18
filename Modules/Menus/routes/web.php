<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Menus\Http\Controllers\MenuItemController;
use Modules\Menus\Http\Controllers\MenusController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Menus'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('menus/data', [MenusController::class, 'data'])->name('menus.data');
        Route::get('menus/{menu}/workspace', [MenusController::class, 'workspace'])->name('menus.workspace');
        Route::put('menus/{menu}/reorder', [MenusController::class, 'reorder'])->name('menus.reorder');
        Route::resource('menus', MenusController::class)->except('show');
        Route::resource('menus.items', MenuItemController::class)->except(['index', 'show'])->scoped();
    });
