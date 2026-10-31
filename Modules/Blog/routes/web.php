<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogCategoryController;
use Modules\Blog\Http\Controllers\BlogContentController;
use Modules\Blog\Http\Controllers\BlogTagController;
use Modules\Blog\Http\Controllers\PublicBlogController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Blog'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('blog/translations/{translation}/edit', [BlogContentController::class, 'translationEdit'])->name('blog.translations.edit');
        Route::get('blog/categories/translations/{translation}/edit', [BlogCategoryController::class, 'translationEdit'])->name('blog.categories.translations.edit');
        Route::get('blog/data', [BlogContentController::class, 'data'])->name('blog.data');
        Route::get('blog/categories/data', [BlogCategoryController::class, 'data'])->name('blog.categories.data');
        Route::get('blog/tags/data', [BlogTagController::class, 'data'])->name('blog.tags.data');
        Route::resource('blog/categories', BlogCategoryController::class)->except('show')->parameters(['categories' => 'category'])->names('blog.categories');
        Route::resource('blog/tags', BlogTagController::class)->except('show')->parameters(['tags' => 'tag'])->names('blog.tags');
        Route::resource('blog', BlogContentController::class)->except('show');
    });

Route::middleware('module.enabled:Blog')->group(function (): void {
    Route::get('/{lang}/blog', [PublicBlogController::class, 'index'])->where('lang', '[a-z]{2}')->name('blog.localized.index');
    Route::get('/{lang}/blog/{slug}', [PublicBlogController::class, 'show'])->where('lang', '[a-z]{2}')->name('blog.localized.show');
    Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.public.index');
    Route::get('/blog/{slug}', [PublicBlogController::class, 'show'])->name('blog.public.show');
});
