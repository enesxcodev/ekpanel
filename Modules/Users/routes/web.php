<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\AuthController;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('me', [AuthController::class, 'current'])->name('current');
Route::post('email/verification-notification', [AuthController::class, 'sendVerification'])->middleware('throttle:6,1')->name('verification.send');
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1')->name('password.email');
Route::get('reset-password/{token}', [AuthController::class, 'resetContext'])->middleware('throttle:10,1')->name('password.reset');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
