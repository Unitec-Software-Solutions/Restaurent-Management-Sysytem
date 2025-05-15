<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GoodReceivedNoteController;
use App\Http\Controllers\GoodReceivedNoteItemController;
use App\Http\Controllers\InventoryTransactionController;

use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Inventory routes 
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    
    // Dashboard routes
    Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');
    
    // Item routes
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::get('/create', [ItemController::class, 'create'])->name('create');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('edit');
        Route::put('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
    });

    // Stock routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/store', [StockController::class, 'store'])->name('store');
        Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
        Route::put('/{stock}', [StockController::class, 'update'])->name('update');
        Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');
    });

    // Transaction routes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [InventoryTransactionController::class, 'index'])->name('index');
        Route::get('/{transaction}', [InventoryTransactionController::class, 'show'])->name('show');
    });
    
    // GRN routes 
    Route::prefix('grn')->name('grn.')->group(function () {
        Route::resource('/', GoodReceivedNoteController::class)->except(['create']);
        Route::get('/create', [GoodReceivedNoteController::class, 'create'])->name('create');
        Route::post('/{grn}/finalize', [GoodReceivedNoteController::class, 'finalize'])->name('finalize');
        
        // GRN Items routes
        Route::post('/{grn}/items', [GoodReceivedNoteItemController::class, 'store'])->name('items.store');
        Route::put('/items/{item}', [GoodReceivedNoteItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [GoodReceivedNoteItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/items/{item}/quality-check', [GoodReceivedNoteItemController::class, 'qualityCheck'])->name('items.quality-check');
    });
    
});

// Reservation routes
//  Route::get('/reservation/start', [ReservationsController::class, 'start'])->name('reservation.start');
//  Route::post('/reservation/check-phone', [ReservationsController::class, 'checkPhone'])->name('reservation.check-phone');
//  Route::post('/reservations/proceed-as-guest', [ReservationsController::class, 'proceedAsGuest'])->name('reservations.proceed-as-guest');
//  Route::get('/reservations/choose-action', [ReservationsController::class, 'chooseAction'])->name('reservations.choose-action');
//  Route::get('/reservation/create', [ReservationsController::class, 'create'])->name('reservation.create');
//  Route::post('/reservation/store', [ReservationsController::class, 'store'])->name('reservation.store');
//  Route::get('/reservation/{id}/summary', [ReservationsController::class, 'summary'])->name('reservation.summary');
//  Route::post('/reservation/{id}/confirm', [ReservationsController::class, 'confirm'])->name('reservation.confirm');


// Order routes
// Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
// Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');


// Other routes
// Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
// Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');

// Reservation Routes
Route::prefix('reservations')->name('reservations.')->group(function () {
    // Main reservation routes first
    Route::get('/create', [ReservationController::class, 'create'])->name('create');
    Route::get('/edit', [ReservationController::class, 'edit'])->name('edit');
    Route::get('/review', [ReservationController::class, 'review'])->name('review.get');
    Route::post('/review', [ReservationController::class, 'review'])->name('review');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');
    Route::post('/waitlist', [ReservationController::class, 'joinWaitlist'])->name('waitlist');
    Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');

    // Phone verification routes
    Route::get('/check-phone', [ReservationController::class, 'showPhoneCheck'])->name('check-phone-form');
    Route::post('/check-phone', [ReservationController::class, 'checkPhone'])->name('check-phone');
    Route::post('/guest', [ReservationController::class, 'proceedAsGuest'])->name('guest');
    Route::post('/user', [ReservationController::class, 'proceedAsUser'])->name('user');

    // Parameterized routes last
    Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary')->where('reservation', '[0-9]+');
    Route::post('/{reservation}/payment', [ReservationController::class, 'processPayment'])->name('payment')->where('reservation', '[0-9]+');
    Route::delete('/{reservation}', [ReservationController::class, 'cancel'])->name('cancel')->where('reservation', '[0-9]+');
    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show')->where('reservation', '[0-9]+');
    Route::get('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
});

// Admin Routes
//  Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
//      // Reservation Management
//      Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
//      Route::get('/reservations/pending', [AdminReservationController::class, 'pending'])->name('reservations.pending');
//      Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
//      Route::post('/reservations/{reservation}/confirm', [AdminReservationController::class, 'confirm'])->name('reservations.confirm');
//      Route::post('/reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');
//      Route::get('/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])->name('reservations.edit');
//      Route::put('/reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
//  });


