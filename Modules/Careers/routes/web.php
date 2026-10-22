<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Careers\Http\Controllers\JobApplicationController;
use Modules\Careers\Http\Controllers\JobPostingController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Careers'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.careers.')
    ->group(function (): void {
        Route::get('careers/applications', [JobApplicationController::class, 'index'])->name('applications.index');
        Route::get('careers/applications/create', [JobApplicationController::class, 'create'])->name('applications.create');
        Route::post('careers/applications', [JobApplicationController::class, 'store'])->name('applications.store');
        Route::get('careers', [JobPostingController::class, 'index'])->name('index');
        Route::get('careers/data', [JobPostingController::class, 'data'])->name('data');
        Route::get('careers/create', [JobPostingController::class, 'create'])->name('create');
        Route::post('careers', [JobPostingController::class, 'store'])->name('store');
        Route::get('careers/{career}/edit', [JobPostingController::class, 'edit'])->name('edit');
        Route::put('careers/{career}', [JobPostingController::class, 'update'])->name('update');
        Route::delete('careers/{career}', [JobPostingController::class, 'destroy'])->name('destroy');
    });
