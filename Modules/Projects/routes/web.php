<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\ProjectCategoryController;
use Modules\Projects\Http\Controllers\ProjectController;
use Modules\Projects\Models\ProjectCategoryTranslation;
use Modules\Projects\Models\ProjectTranslation;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Projects'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('projects/translations/{translation}', fn (ProjectTranslation $translation) => redirect()->route('admin.projects.edit', ['project' => $translation->project_id, 'lang' => $translation->language->code]))->name('projects.translations.edit');
        Route::get('projects/categories/translations/{translation}', fn (ProjectCategoryTranslation $translation) => redirect()->route('admin.projects.categories.edit', ['category' => $translation->project_category_id, 'lang' => $translation->language->code]))->name('projects.categories.translations.edit');
        Route::get('projects/data', [ProjectController::class, 'data'])->name('projects.data');
        Route::get('projects/categories/data', [ProjectCategoryController::class, 'data'])->name('projects.categories.data');
        Route::resource('projects/categories', ProjectCategoryController::class)->except('show')->parameters(['categories' => 'category'])->names('projects.categories');
        Route::resource('projects', ProjectController::class)->except('show')->parameters(['projects' => 'project']);
    });
