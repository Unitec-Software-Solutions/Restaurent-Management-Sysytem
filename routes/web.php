<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemTransactionController;
use App\Http\Controllers\OrderController;

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
        
        // Reservation Management
        Route::resource('reservations', AdminReservationController::class);

        // Inventory routes
        Route::prefix('inventory')->name('inventory.')->group(function () {
            // Dashboard
            Route::get('/', [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

            // Inventory Item Routes - Web Interface
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', [ItemMasterController::class, 'index'])->name('index');
                Route::get('/create', [ItemMasterController::class, 'create'])->name('create');
                Route::post('/', [ItemMasterController::class, 'store'])->name('store');
                Route::get('/{item}', [ItemMasterController::class, 'show'])->whereNumber('item')->name('show');
                Route::get('/{item}/edit', [ItemMasterController::class, 'edit'])->name('edit');
                Route::put('/{item}', [ItemMasterController::class, 'update'])->name('update');
                Route::delete('/{item}', [ItemMasterController::class, 'destroy'])->name('destroy');
                Route::get('/create-template/{index}/', [ItemMasterController::class, 'getItemFormPartial'])->name('form-partial');
            });

            // Inventory API Routes - JSON endpoints
            Route::prefix('api/items')->name('api.items.')->group(function () {
                Route::get('/', [ItemMasterController::class, 'index']);
                Route::post('/', [ItemMasterController::class, 'store']);
                Route::get('/{item}', [ItemMasterController::class, 'show']);
                Route::put('/{item}', [ItemMasterController::class, 'update']);
                Route::delete('/{item}', [ItemMasterController::class, 'destroy']);
            });

            // Stock Management Routes
            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
                Route::get('/{transaction}', [ItemTransactionController::class, 'show'])->whereNumber('transaction')->name('show');
                Route::get('/{transaction}/edit', [ItemTransactionController::class, 'edit'])->name('edit');
                Route::put('/{transaction}', [ItemTransactionController::class, 'update'])->name('update');
                Route::delete('/{transaction}', [ItemTransactionController::class, 'destroy'])->name('destroy');

                // Stock API Routes
                Route::prefix('api')->name('api.')->group(function () {
                    Route::get('/transactions', [ItemTransactionController::class, 'transactions'])->name('transactions');
                });

                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });

            // Categories Management
            Route::resource('categories', ItemCategoryController::class);
            
            // Categories API Routes
            Route::prefix('api/categories')->name('api.categories.')->group(function () {
                Route::get('/', [ItemCategoryController::class, 'index']);
                Route::post('/', [ItemCategoryController::class, 'store']);
                Route::get('/{category}', [ItemCategoryController::class, 'show']);
                Route::put('/{category}', [ItemCategoryController::class, 'update']);
                Route::delete('/{category}', [ItemCategoryController::class, 'destroy']);
            });
        });

        // Order Management
        Route::get('/orders', function () {return view('admin.orders.index');})->name('orders.index');
        
        // Reports
        Route::get('/reports', function () {return view('admin.reports.index');})->name('reports.index');
        
        // Customer Management
        Route::get('/customers', function () {return view('admin.customers.index');})->name('customers.index');
        
        // Digital Menu
        Route::get('/digital-menu', function () {return view('admin.digital-menu.index');})->name('digital-menu.index');
        
        // Settings
        Route::get('/settings', function () {return view('admin.settings.index');})->name('settings.index');

        // User Management
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');
    });
});

// API routes outside admin prefix for external access
Route::prefix('api')->middleware('auth:admin')->group(function () {
    Route::prefix('inventory')->group(function () {
        Route::get('/items', [ItemMasterController::class, 'index']);
        Route::post('/items', [ItemMasterController::class, 'store']);
        Route::get('/items/{item}', [ItemMasterController::class, 'show']);
        Route::put('/items/{item}', [ItemMasterController::class, 'update']);
        Route::delete('/items/{item}', [ItemMasterController::class, 'destroy']);
        
        Route::get('/categories', [ItemCategoryController::class, 'index']);
        Route::get('/transactions', [ItemTransactionController::class, 'transactions']);
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
    Route::get('/{reservation}/payment', [ReservationController::class, 'payment'])->name('payment');
});

// Order routes
Route::resource('orders', OrderController::class);
Route::get('/orders/{order}/payment', [OrderController::class, 'payment'])
    ->name('orders.payment');
Route::get('/orders/create', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.create');