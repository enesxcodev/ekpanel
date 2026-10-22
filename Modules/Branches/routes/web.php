<?php

use App\Http\Middleware\Admin\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;
use Modules\Branches\Http\Controllers\BranchCategoryController;
use Modules\Branches\Http\Controllers\BranchController;

Route::middleware(['auth:admin', EnsureAdminIsActive::class, 'module.enabled:Branches'])
    ->prefix((trim((string) config('app.route_files.admin_prefix')) ?: 'admin').'/branches')
    ->name('admin.branches.')
    ->group(function (): void {
        Route::get('categories/data', [BranchCategoryController::class, 'data'])->middleware('can:branches.categories.view')->name('categories.data');
        Route::get('categories', [BranchCategoryController::class, 'index'])->middleware('can:branches.categories.view')->name('categories.index');
        Route::get('categories/create', [BranchCategoryController::class, 'create'])->middleware('can:branches.categories.create')->name('categories.create');
        Route::post('categories', [BranchCategoryController::class, 'store'])->middleware('can:branches.categories.create')->name('categories.store');
        Route::get('categories/{category}/edit', [BranchCategoryController::class, 'edit'])->middleware('can:branches.categories.update')->name('categories.edit');
        Route::put('categories/{category}', [BranchCategoryController::class, 'update'])->middleware('can:branches.categories.update')->name('categories.update');
        Route::delete('categories/{category}', [BranchCategoryController::class, 'destroy'])->middleware('can:branches.categories.delete')->name('categories.destroy');

        Route::get('data', [BranchController::class, 'data'])->middleware('can:branches.view')->name('data');
        Route::get('location-options', [BranchController::class, 'locationOptions'])->middleware('can:branches.view')->name('location-options');
        Route::get('/', [BranchController::class, 'index'])->middleware('can:branches.view')->name('index');
        Route::get('create', [BranchController::class, 'create'])->middleware('can:branches.create')->name('create');
        Route::post('/', [BranchController::class, 'store'])->middleware('can:branches.create')->name('store');
        Route::get('{branch}/edit', [BranchController::class, 'edit'])->middleware('can:branches.update')->name('edit');
        Route::put('{branch}', [BranchController::class, 'update'])->middleware('can:branches.update')->name('update');
        Route::delete('{branch}', [BranchController::class, 'destroy'])->middleware('can:branches.delete')->name('destroy');
    });
