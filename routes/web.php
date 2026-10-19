<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\PublicFormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
