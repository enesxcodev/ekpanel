<?php

use App\Http\Controllers\Admin\Roles\AdminRoleController;
use App\Http\Controllers\Admin\Settings\SettingConfigurationController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\Settings\SettingDefinitionController;
use App\Http\Controllers\Admin\Settings\SettingSectionController;
use App\Http\Controllers\Admin\Users\AdminUserController;
use App\Http\Controllers\AdminAuth\AdminAuthController;
use App\Http\Middleware\AdminAuth\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;

Route::prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')->name('admin.')->group(function (): void {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', EnsureAdminIsActive::class])->group(function (): void {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::resource('roles', AdminRoleController::class)->only(['index', 'edit', 'update'])->middleware('can:manage-admin-roles');

        Route::prefix('settings')->name('settings.')->group(function (): void {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/sections/{section}/values', [SettingController::class, 'update'])->name('update');

            Route::middleware('can:manage-setting-definitions')->group(function (): void {
                Route::get('/configuration', [SettingConfigurationController::class, 'index'])->name('configuration.index');
                Route::resource('sections', SettingSectionController::class)->only(['store', 'update', 'destroy']);
                Route::resource('definitions', SettingDefinitionController::class)->except(['index', 'show']);
            });
        });

        Route::get('/users/data', [AdminUserController::class, 'data'])->name('users.data');
        Route::resource('users', AdminUserController::class)
            ->parameters(['users' => 'admin'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });
});
