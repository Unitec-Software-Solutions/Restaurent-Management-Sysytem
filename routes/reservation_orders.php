<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;

// Reservation Orders Management Routes
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('orders/reservations')->name('orders.reservations.')->group(function () {
        Route::get('/', [OrderController::class, 'reservations'])->name('index');
        Route::get('/create', [OrderController::class, 'createReservationOrder'])->name('create');
        Route::post('/store', [OrderController::class, 'storeReservationOrder'])->name('store');
        Route::get('/{order}/edit', [OrderController::class, 'editReservationOrder'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'updateReservationOrder'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroyReservationOrder'])->name('destroy');
    });
});
