<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Forms\FormController;
use App\Http\Controllers\Admin\Forms\FormSubmissionController;
use App\Http\Controllers\Admin\Modules\ModuleController;
use App\Http\Controllers\Admin\Notifications\NotificationController;
use App\Http\Controllers\Admin\Roles\AdminRoleController;
use App\Http\Controllers\Admin\Settings\SettingConfigurationController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\Settings\SettingDefinitionController;
use App\Http\Controllers\Admin\Settings\SettingSectionController;
use App\Http\Controllers\Admin\Users\AdminUserController;
use App\Http\Controllers\MediaController;
use App\Http\Middleware\Admin\EnsureAdminIsActive;
use App\Http\Middleware\Admin\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix(trim((string) config('app.route_files.admin_prefix')) ?: 'admin')->name('admin.')->group(function (): void {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', EnsureAdminIsActive::class])->group(function (): void {
        Route::prefix('notifications')->name('notifications.')->group(function (): void {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/data', [NotificationController::class, 'data'])->name('data');
            Route::get('/header', [NotificationController::class, 'header'])->name('header');
            Route::patch('/', [NotificationController::class, 'update'])->name('update');
            Route::get('/{notification}', [NotificationController::class, 'show'])->whereUuid('notification')->name('show');
            Route::post('/{notification}/open', [NotificationController::class, 'open'])->whereUuid('notification')->name('open');
            Route::patch('/{notification}/read', [NotificationController::class, 'read'])->whereUuid('notification')->name('read');
        });
        Route::prefix('forms')->name('forms.')->group(function (): void {
            Route::get('submissions', [FormSubmissionController::class, 'index'])->name('submissions.index');
            Route::get('submissions/data', [FormSubmissionController::class, 'data'])->name('submissions.data');
            Route::get('submissions/{submission}', [FormSubmissionController::class, 'show'])->name('submissions.show');
            Route::patch('submissions/{submission}', [FormSubmissionController::class, 'update'])->name('submissions.update');
            Route::delete('submissions/{submission}', [FormSubmissionController::class, 'destroy'])->name('submissions.destroy');
            Route::get('submissions/{submission}/files/{field}', [FormSubmissionController::class, 'download'])->name('submissions.download');
            Route::post('import', [FormController::class, 'import'])->name('import');
            Route::get('data', [FormController::class, 'data'])->name('data');
        });
        Route::resource('forms', FormController::class)->except('show')->parameters(['forms' => 'form']);
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::prefix('media')->name('media.')->group(function (): void {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::get('/data', [MediaController::class, 'data'])->name('data');
            Route::post('/files', [MediaController::class, 'store'])->middleware('throttle:30,1')->name('store');
            Route::post('/sync', [MediaController::class, 'synchronize'])->middleware('throttle:4,1')->name('sync');
            Route::get('/files/{mediaFile}', [MediaController::class, 'show'])->name('show');
            Route::patch('/files/{mediaFile}', [MediaController::class, 'update'])->name('update');
            Route::delete('/files/{mediaFile}', [MediaController::class, 'destroy'])->name('destroy');
            Route::post('/files/{mediaFile}/restore', [MediaController::class, 'restore'])->withTrashed()->name('restore');
            Route::put('/files/{mediaFile}/favorite', [MediaController::class, 'favorite'])->name('favorite');
            Route::get('/files/{mediaFile}/download', [MediaController::class, 'download'])->name('download');
            Route::post('/folders', [MediaController::class, 'storeFolder'])->name('folders.store');
            Route::patch('/folders/{folder}', [MediaController::class, 'updateFolder'])->name('folders.update');
            Route::delete('/folders/{folder}', [MediaController::class, 'destroyFolder'])->name('folders.destroy');
        });

        Route::resource('roles', AdminRoleController::class)->only(['index', 'edit', 'update'])->middleware('can:manage-admin-roles');

        Route::prefix('modules')->name('modules.')->middleware(EnsureSuperAdmin::class)->group(function (): void {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::get('/catalog', [ModuleController::class, 'create'])->name('create');
            Route::get('/catalog/{id}', [ModuleController::class, 'show'])->name('show');
            Route::get('/installations/{installation}', [ModuleController::class, 'installation'])->whereUuid('installation')->name('installations.show');
            Route::post('/', [ModuleController::class, 'store'])->middleware('throttle:6,1')->name('store');
            Route::post('/{module}/enable', [ModuleController::class, 'enable'])->name('enable');
            Route::post('/{module}/disable', [ModuleController::class, 'disable'])->name('disable');
            Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy');
        });

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
