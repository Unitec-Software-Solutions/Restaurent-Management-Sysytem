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
// Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
// Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

// Other routes
// Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
// Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');

