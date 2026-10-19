<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\PublicFormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('forms/{form:key}', [PublicFormController::class, 'show'])->name('forms.show');
Route::post('forms/{form:key}', [PublicFormSubmissionController::class, 'store'])->middleware('throttle:form-submissions')->name('forms.submit');

Route::get('/', [HomeController::class, 'index'])->name('home');
