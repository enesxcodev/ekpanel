<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Faqs\Http\Controllers\FaqCategoryController;
use Modules\Faqs\Http\Controllers\FaqController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Faqs'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('faqs/categories/data', [FaqCategoryController::class, 'data'])->name('faqs.categories.data');
        Route::get('faqs/data', [FaqController::class, 'data'])->name('faqs.data');
        Route::resource('faqs/categories', FaqCategoryController::class)
            ->except('show')
            ->parameters(['categories' => 'category'])
            ->names('faqs.categories');
        Route::resource('faqs', FaqController::class)->except('show');
    });
