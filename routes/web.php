<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\CustomerAuthController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/reservation/start', [ReservationsController::class, 'start']);
Route::post('/reservation/check-phone', [ReservationsController::class, 'checkPhone']);
Route::get('/reservations', [ReservationsController::class, 'index']);
Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');