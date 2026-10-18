<?php

use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Http\Controllers\TestimonialController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Testimonials'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('testimonials/data', [TestimonialController::class, 'data'])->name('testimonials.data');
        Route::resource('testimonials', TestimonialController::class)->except('show');
    });
