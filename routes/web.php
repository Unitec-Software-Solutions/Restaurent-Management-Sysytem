<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GoodReceivedNoteController;
use App\Http\Controllers\GoodReceivedNoteItemController;
use App\Http\Controllers\InventoryTransactionController;

use App\Http\Controllers\AdminReservationController;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Inventory routes with proper naming
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    // Dashboard routes
    Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');
    
    // Item routes
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    
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

// Reservation Routes - Keep this main group as it contains all necessary routes
Route::prefix('reservations')->name('reservations.')->group(function () {
    // Main reservation routes first
    Route::get('/create', [ReservationController::class, 'create'])->name('create');
    Route::get('/edit', [ReservationController::class, 'edit'])->name('edit');
    Route::get('/review', [ReservationController::class, 'review'])->name('review.get');
    Route::post('/review', [ReservationController::class, 'review'])->name('review');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');
    Route::get('/waitlist/{waitlist}', [ReservationController::class, 'waitlist'])->name('waitlist');
    Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');

    // Phone verification routes
    Route::get('/check-phone', [ReservationController::class, 'showPhoneCheck'])->name('check-phone-form');
    Route::post('/check-phone', [ReservationController::class, 'checkPhone'])->name('check-phone');
    Route::post('/guest', [ReservationController::class, 'proceedAsGuest'])->name('guest');
    Route::post('/user', [ReservationController::class, 'proceedAsUser'])->name('user');

    // Parameterized routes last
    Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary')->where('reservation', '[0-9]+');
    Route::post('/{reservation}/payment', [ReservationController::class, 'processPayment'])->name('payment')->where('reservation', '[0-9]+');
    Route::get('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel')->where('reservation', '[0-9]+');
    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show')->where('reservation', '[0-9]+');
    Route::get('/{reservation}/show', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
});

// Admin Routes
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/reservations/{reservation}', [AdminReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations', [AdminReservationController::class, 'store'])->name('reservations.store');
});


