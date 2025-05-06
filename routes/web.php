<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Reservation routes
Route::get('/reservation/start', [ReservationsController::class, 'start'])->name('reservation.start');
Route::post('/reservation/check-phone', [ReservationsController::class, 'checkPhone'])->name('reservation.check-phone');
Route::post('/reservations/proceed-as-guest', [ReservationsController::class, 'proceedAsGuest'])->name('reservations.proceed-as-guest');
Route::get('/reservations/choose-action', [ReservationsController::class, 'chooseAction'])->name('reservations.choose-action');
Route::get('/reservation/create', [ReservationsController::class, 'create'])->name('reservation.create');
Route::post('/reservation/store', [ReservationsController::class, 'store'])->name('reservation.store');
Route::get('/reservation/{id}/summary', [ReservationsController::class, 'summary'])->name('reservation.summary');
Route::post('/reservation/{id}/confirm', [ReservationsController::class, 'confirm'])->name('reservation.confirm');

// Order routes
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

// Other routes
Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');