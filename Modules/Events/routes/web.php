<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\EventController;
use Modules\Events\Http\Controllers\EventRegistrationController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Events'])
    ->prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')
    ->name('admin.events.')
    ->group(function (): void {
        Route::get('events/registrations', [EventRegistrationController::class, 'index'])->name('registrations.index');
        Route::get('events/registrations/create', [EventRegistrationController::class, 'create'])->name('registrations.create');
        Route::post('events/registrations', [EventRegistrationController::class, 'store'])->name('registrations.store');
        Route::get('events', [EventController::class, 'index'])->name('index');
        Route::get('events/data', [EventController::class, 'data'])->name('data');
        Route::get('events/create', [EventController::class, 'create'])->name('create');
        Route::post('events', [EventController::class, 'store'])->name('store');
        Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::put('events/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->name('destroy');
    });
