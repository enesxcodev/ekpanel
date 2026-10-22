<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Konum\Http\Controllers\KonumController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Konum'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.konum.')
    ->group(function (): void {
        Route::get('konum', [KonumController::class, 'index'])->name('index');
        Route::get('konum/{entity}/data', [KonumController::class, 'data'])
            ->whereIn('entity', ['cities', 'towns', 'districts', 'neighbourhoods', 'streets'])
            ->name('data');
    });
