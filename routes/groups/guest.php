<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guest\GuestController;

// Guest routes - these will inherit web middleware when included in web.php
Route::prefix('guest')->name('guest.')->group(function () {
    // Menu routes
    Route::get('menu/branches', [GuestController::class, 'viewMenu'])->name('menu.branch-selection');
    Route::get('menu/{branchId?}', [GuestController::class, 'viewMenu'])->name('menu.view');
    Route::get('menu/{branchId}/date/{date}', [GuestController::class, 'viewMenuByDate'])->name('menu.date');
    Route::get('menu/{branchId}/special', [GuestController::class, 'viewSpecialMenu'])->name('menu.special');
    
    // Cart routes
    Route::post('cart/add', [GuestController::class, 'addToCart'])->name('cart.add');
    Route::post('cart/update', [GuestController::class, 'updateCart'])->name('cart.update');
    Route::delete('cart/remove/{itemId}', [GuestController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('cart', [GuestController::class, 'viewCart'])->name('cart.view');
    Route::delete('cart/clear', [GuestController::class, 'clearCart'])->name('cart.clear');
    
    // Order routes
    Route::post('order/create', [GuestController::class, 'createOrder'])->name('order.create');
    Route::get('order/{orderId}/confirmation/{token}', [GuestController::class, 'orderConfirmation'])->name('order.confirmation');
    Route::get('order/{orderNumber}/track', [GuestController::class, 'trackOrder'])->name('order.track');
    Route::get('order/{orderNumber}/details', [GuestController::class, 'orderDetails'])->name('order.details');
    
    // Reservation routes
    Route::get('reservations/create/{branchId?}', [GuestController::class, 'showReservationForm'])->name('reservations.create');
    Route::post('reservations/store', [GuestController::class, 'createReservation'])->name('reservations.store');
    Route::get('reservations/{confirmationNumber}/confirmation', [GuestController::class, 'reservationConfirmation'])->name('reservations.confirmation');
    Route::get('reservations/{reservationId}/confirmation/{token}', [GuestController::class, 'reservationConfirmationById'])->name('reservation.confirmation');
    
    // Session info
    Route::get('session/info', [GuestController::class, 'sessionInfo'])->name('session.info');
});
