<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;

use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Models\ItemMaster;



// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Customer dashboard: show all reservations by phone number
Route::get('/customer-dashboard', [CustomerDashboardController::class, 'showReservationsByPhone'])->name('customer.dashboard');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication (login/logout) should be outside the auth:admin middleware
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    // New logout confirmation page and action
    Route::get('/logout', [AdminAuthController::class, 'adminLogoutPage'])->name('logout.page');
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // Protected admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('reservations', AdminReservationController::class);

        // Inventory Item Routes
        Route::prefix('inventory')->name('inventory.')->group(function () {

            Route::get('/ ', [ItemMasterController::class, 'index'])->name('index');
            

            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', [ItemMasterController::class, 'index'])->name('index');
                Route::get('/create', [ItemMasterController::class, 'create'])->name('create');
                Route::post('/', [ItemMasterController::class, 'store'])->name('store');
                Route::get('/{item}', [ItemMasterController::class, 'show'])->name('show');
                Route::get('/{item}/edit', [ItemMasterController::class, 'edit'])->name('edit');
                Route::put('/{item}', [ItemMasterController::class, 'update'])->name('update');
                Route::delete('/{item}', [ItemMasterController::class, 'destroy'])->name('destroy');
            });
            
        });




    // Items routes
    
    
    // Categories routes
    Route::resource('categories', ItemCategoryController::class);


    });
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
