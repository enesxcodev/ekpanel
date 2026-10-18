<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\ProjectCategoryController;
use Modules\Projects\Http\Controllers\ProjectController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Projects'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('projects/data', [ProjectController::class, 'data'])->name('projects.data');
        Route::get('projects/categories/data', [ProjectCategoryController::class, 'data'])->name('projects.categories.data');
        Route::resource('projects/categories', ProjectCategoryController::class)->except('show')->parameters(['categories' => 'category'])->names('projects.categories');
        Route::resource('projects', ProjectController::class)->except('show')->parameters(['projects' => 'project']);
    });
