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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Inventory routes
Route::middleware(['auth:admin'])->prefix('inventory')->name('inventory.')->group(function () {
    // Dashboard
    Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');

    // Items
    Route::resource('items', ItemController::class)->except(['destroy']);
    
    // Stock
    Route::resource('stock', StockController::class);

    // Transactions
    Route::resource('transactions', InventoryTransactionController::class)->only(['index', 'show']);

    // GRN (Good Received Notes)
    Route::prefix('grn')->name('grn.')->group(function () {
        Route::resource('/', GoodReceivedNoteController::class)->except(['create']);
        Route::get('/create', [GoodReceivedNoteController::class, 'create'])->name('create');
        Route::post('/{grn}/finalize', [GoodReceivedNoteController::class, 'finalize'])->name('finalize');

        // GRN Items
        Route::prefix('{grn}/items')->name('items.')->group(function () {
            Route::post('/', [GoodReceivedNoteItemController::class, 'store'])->name('store');
            Route::put('/{item}', [GoodReceivedNoteItemController::class, 'update'])->name('update');
            Route::delete('/{item}', [GoodReceivedNoteItemController::class, 'destroy'])->name('destroy');
            Route::post('/{item}/quality-check', [GoodReceivedNoteItemController::class, 'qualityCheck'])->name('quality-check');
        });
    });
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('reservations', AdminReservationController::class);

    // Authentication
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Reservation routes
Route::prefix('reservations')->name('reservations.')->group(function () {
    // Main reservation routes
    Route::get('/create', [ReservationController::class, 'create'])->name('create');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');
    Route::get('/edit', [ReservationController::class, 'edit'])->name('edit');
    Route::get('/review', [ReservationController::class, 'review'])->name('review.get');
    Route::post('/review', [ReservationController::class, 'review'])->name('review');
    Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');
    // Parameterized reservation routes
    Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary')->where('reservation', '[0-9]+');
    Route::get('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel')->where('reservation', '[0-9]+');
    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show')->where('reservation', '[0-9]+');
    Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('confirm');
});
