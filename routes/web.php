<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\StockController;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    //  Route::get('/items/create', [InventoryItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    //  Route::post('/items', [InventoryItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
});

Route::prefix('inventory/stock')->name('inventory.stock.')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::get('/create', [StockController::class, 'create'])->name('create');
    Route::post('/store', [StockController::class, 'store'])->name('store');
    Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
    Route::put('/{stock}', [StockController::class, 'update'])->name('update');
    Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');
});
