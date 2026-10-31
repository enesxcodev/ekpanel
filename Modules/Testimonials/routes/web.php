<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Http\Controllers\TestimonialController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Testimonials'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('testimonials/data', [TestimonialController::class, 'data'])->name('testimonials.data');
        Route::get('testimonials/translations/{translation}', [TestimonialController::class, 'editTranslation'])->name('testimonials.translation.edit');
        Route::resource('testimonials', TestimonialController::class)->except('show');
    });
