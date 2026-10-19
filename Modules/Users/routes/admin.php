<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UserController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Users'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.web-users.')
    ->group(function (): void {
        Route::get('web-users/data', [UserController::class, 'data'])->name('data');
        Route::resource('web-users', UserController::class)->parameters(['web-users' => 'user'])
            ->only(['index', 'edit', 'update', 'destroy'])->names([
                'index' => 'index', 'edit' => 'edit', 'update' => 'update', 'destroy' => 'destroy',
            ]);
    });
