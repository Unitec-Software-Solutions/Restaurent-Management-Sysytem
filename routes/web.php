<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Inventory Dashboard Routes
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');
    Route::post('/items', [InventoryDashboardController::class, 'storeItem'])->name('items.store');
    Route::put('/items/{item}', [InventoryDashboardController::class, 'updateItem'])->name('items.update');
});
