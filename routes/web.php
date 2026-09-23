<?php

use App\Http\Controllers\ApplicationFormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::post('/applications', [ApplicationFormController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('applications.store');
