<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Redirects\Http\Controllers\RedirectsController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Redirects'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.redirects.')
    ->group(function (): void {
        Route::get('redirects', [RedirectsController::class, 'index'])
            ->middleware('can:redirects.view')
            ->name('index');
    });
