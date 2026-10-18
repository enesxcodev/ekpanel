<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Sector\Http\Controllers\SectorController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Sector'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.sector.')
    ->group(function (): void {
        Route::get('sector/data', [SectorController::class, 'data'])->name('data');
        Route::resource('sector', SectorController::class)->parameters(['sector' => 'sector'])->except('show')->names([
            'index' => 'index', 'create' => 'create', 'store' => 'store',
            'edit' => 'edit', 'update' => 'update', 'destroy' => 'destroy',
        ]);
    });
