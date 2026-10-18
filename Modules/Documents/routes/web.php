<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\DocumentCategoryController;
use Modules\Documents\Http\Controllers\DocumentController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Documents'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('documents/categories/data', [DocumentCategoryController::class, 'data'])->name('documents.categories.data');
        Route::get('documents/data', [DocumentController::class, 'data'])->name('documents.data');
        Route::resource('documents/categories', DocumentCategoryController::class)
            ->except('show')
            ->parameters(['categories' => 'category'])
            ->names('documents.categories');
        Route::resource('documents', DocumentController::class)->except('show');
    });
