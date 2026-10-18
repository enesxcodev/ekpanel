<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Pages\Http\Controllers\PageCategoryController;
use Modules\Pages\Http\Controllers\PageController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Pages'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('pages/data', [PageController::class, 'data'])->name('pages.data');
        Route::get('pages/categories/data', [PageCategoryController::class, 'data'])->name('pages.categories.data');
        Route::resource('pages', PageController::class)->except('show');
        Route::resource('pages/categories', PageCategoryController::class)
            ->except('show')
            ->parameters(['categories' => 'category'])
            ->names('pages.categories');
    });

Route::middleware('module.enabled:Pages')
    ->get('/{path}', [PageController::class, 'show'])
    ->where('path', '.*')
    ->fallback()
    ->name('pages.show');
