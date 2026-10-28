<?php

use App\Http\Controllers\Frontend\SiteFormController;
use App\Http\Controllers\Frontend\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ThemeController::class, 'index'])->name('home');
Route::post('/forms/{form:key}/submit', SiteFormController::class)
    ->middleware('throttle:form-submissions')->name('forms.submit');
