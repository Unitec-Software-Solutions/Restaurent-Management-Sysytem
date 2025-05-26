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

            // Inventory Item Routes
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

            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
                Route::get('/{transaction}', [ItemTransactionController::class, 'show'])->whereNumber('transaction')->name('show');
                Route::get('/{transaction}/edit', [ItemTransactionController::class, 'edit'])->name('edit');
                Route::put('/{transaction}', [ItemTransactionController::class, 'update'])->name('update');
                Route::delete('/{transaction}', [ItemTransactionController::class, 'destroy'])->name('destroy');

                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });

            Route::resource('categories', ItemCategoryController::class);
        });

        // Order Management
        Route::get('/orders', function () {
            return view('admin.orders.index');
        })->name('orders.index');
        
        // Reports
        Route::get('/reports', function () {
            return view('admin.reports.index');
        })->name('reports.index');
        
        // Customer Management
        Route::get('/customers', function () {
            return view('admin.customers.index');
        })->name('customers.index');
        
        // Digital Menu
        Route::get('/digital-menu', function () {
            return view('admin.digital-menu.index');
        })->name('digital-menu.index');
        
        // Settings
        Route::get('/settings', function () {
            return view('admin.settings.index');
        })->name('settings.index');

        // User Management
        Route::get('/profile', function () {
            return view('admin.profile.index');
        })->name('users.index');


    });
});

// =========================
// RESERVATION ORDERS (Customer/Staff)
// =========================
Route::prefix('reservations')->name('reservations.')->group(function () {
    Route::get('/create', [ReservationController::class, 'create'])->name('create');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');
    Route::get('/{reservation}/payment', [ReservationController::class, 'payment'])->name('payment');
    Route::post('/{reservation}/process-payment', [ReservationController::class, 'processPayment'])->name('process-payment');
    Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('confirm');
    Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary');
    Route::match(['get', 'post'], '/review', [ReservationController::class, 'review'])->name('review');
    Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
});

// =========================
// TAKEAWAY ORDERS (Customer/Staff)
// =========================
Route::prefix('orders/takeaway')->name('orders.takeaway.')->group(function() {
    Route::get('/create', [OrderController::class, 'createTakeaway'])->name('create');
    Route::post('/store', [OrderController::class, 'storeTakeaway'])->name('store');
    Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
});

// =========================
// ADMIN RESERVATION ORDERS
// =========================
Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::prefix('reservations/{reservation}/orders')->name('reservations.orders.')->group(function () {
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
        Route::post('/{order}/submit', [OrderController::class, 'submit'])->name('submit');
        // ...admin-specific actions (cancel, status change, etc.)...
    });
});

// =========================
// ADMIN TAKEAWAY ORDERS
// =========================
Route::prefix('admin/orders/takeaway')->name('admin.orders.takeaway.')->middleware('auth:admin')->group(function () {
    Route::get('/create', [OrderController::class, 'createAdminTakeaway'])->name('create');
    Route::post('/store', [OrderController::class, 'storeAdminTakeaway'])->name('store');
    Route::get('/{order}/edit', [OrderController::class, 'editAdminTakeaway'])->name('edit');
    Route::put('/{order}/update', [OrderController::class, 'updateAdminTakeaway'])->name('update');
    Route::get('/{order}/summary', [OrderController::class, 'summaryAdminTakeaway'])->name('summary');
    Route::post('/{order}/submit', [OrderController::class, 'submitAdminTakeaway'])->name('submit');
    Route::get('/{order}/show', [OrderController::class, 'showAdminTakeaway'])->name('show');
    Route::delete('/{order}/destroy', [OrderController::class, 'destroyAdminTakeaway'])->name('destroy');
    // ...admin-specific actions (status, branch filter, etc.)...
});

// =========================
// ADMIN ORDER MANAGEMENT VIEWS
// =========================
// Route::prefix('admin/orders')->name('admin.orders.')->middleware(['auth:admin'])->group(function () {
//     Route::get('/', [AdminOrderController::class, 'index'])->name('index');
//     Route::get('/branch/{branch}', [AdminOrderController::class, 'branchOrders'])->name('branch');
//     Route::get('/{order}/show', [AdminOrderController::class, 'show'])->name('show');
//     Route::get('/{order}/edit', [AdminOrderController::class, 'edit'])->name('edit');
//     Route::put('/{order}/update', [AdminOrderController::class, 'update'])->name('update');
//     Route::delete('/{order}/destroy', [AdminOrderController::class, 'destroy'])->name('destroy');
//     // ...status change, cancel, etc...
// });

// Add destroy route for reservation orders (dine-in)
Route::delete('/{order}/delete', [OrderController::class, 'destroy'])->name('destroy');

// Add destroy route for takeaway orders (customer/staff)
Route::delete('orders/takeaway/{order}/delete', [OrderController::class, 'destroyTakeaway'])->name('orders.destroy');

// Takeaway Orders Index (Customer/Staff)
Route::get('orders', [OrderController::class, 'index'])->name('orders.index');



// Takeaway Order Edit (Customer/Staff)
Route::get('orders/{order}/edit', [OrderController::class, 'editTakeaway'])->name('orders.edit');

// All Orders Index (Customer/Staff)
Route::get('orders/all', [OrderController::class, 'allOrders'])->name('orders.all');

// Customer Routes
Route::prefix('reservations')->group(function() {
    Route::resource('reservations', ReservationController::class);
    Route::match(['get', 'post'], '/review', [ReservationController::class, 'review'])->name('reservations.review');
    Route::post('/{reservation}/payment', [ReservationController::class, 'processPayment']);
});
Route::prefix('orders')->group(function() {
    Route::resource('orders', OrderController::class);
    Route::get('orders/{order}/summary', [OrderController::class, 'summary'])->name('orders.summary');
    Route::get('/reservations/{reservation}/add-order', [OrderController::class, 'addToReservation'])->name('orders.add_to_reservation');
});


// Admin Routes
Route::prefix('admin')->middleware('auth:admin')->group(function() {
    Route::resource('reservations', AdminReservationController::class);

});


