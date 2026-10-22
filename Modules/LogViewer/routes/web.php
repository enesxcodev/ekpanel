<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\LogViewer\Http\Controllers\LogViewerController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:LogViewer'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.logviewer.')
    ->group(function (): void {
        Route::get('log-viewer', LogViewerController::class)
            ->middleware('can:logviewer.view')
            ->name('index');
    });
