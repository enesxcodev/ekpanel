<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Team\Http\Controllers\TeamMemberController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Team'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.team.')
    ->group(function (): void {
        Route::get('team/data', [TeamMemberController::class, 'data'])->name('data');
        Route::resource('team', TeamMemberController::class)->parameters(['team' => 'member'])->except('show')->names([
            'index' => 'index', 'create' => 'create', 'store' => 'store',
            'edit' => 'edit', 'update' => 'update', 'destroy' => 'destroy',
        ]);
    });
