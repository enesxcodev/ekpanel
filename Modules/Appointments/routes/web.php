<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Appointments\Http\Controllers\AppointmentController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Appointments'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.appointments.')
    ->group(function (): void {
        Route::get('appointments', [AppointmentController::class, 'index'])->name('index');
        Route::get('appointments/data', [AppointmentController::class, 'data'])->name('data');
        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('store');
        Route::get('appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
    });
