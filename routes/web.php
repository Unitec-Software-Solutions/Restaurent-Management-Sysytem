<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\SupplierController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Customer Routes
Route::middleware(['web'])->group(function () {
    // Customer Dashboard
    Route::get('/customer-dashboard', [CustomerDashboardController::class, 'showReservationsByPhone'])
         ->name('customer.dashboard');

    // Reservations
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/create', [ReservationController::class, 'create'])->name('create');
        Route::post('/store', [ReservationController::class, 'store'])->name('store');
        Route::get('/{reservation}/payment', [ReservationController::class, 'payment'])->name('payment');
        Route::post('/{reservation}/process-payment', [ReservationController::class, 'processPayment'])->name('process-payment');
        Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('confirm');
        Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary');
        Route::match(['get', 'post'], '/review', [ReservationController::class, 'review'])->name('review');
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
        Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/all', [OrderController::class, 'allOrders'])->name('all');
        Route::post('/update-cart', [OrderController::class, 'updateCart'])->name('update-cart');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        
        // Takeaway Orders
        Route::prefix('takeaway')->name('takeaway.')->group(function() {
            Route::get('/create', [OrderController::class, 'createTakeaway'])->name('create');
            Route::post('/store', [OrderController::class, 'storeTakeaway'])->name('store');
            Route::get('/{order}/edit', [OrderController::class, 'editTakeaway'])->name('edit');
            Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
            Route::delete('/{order}/delete', [OrderController::class, 'destroyTakeaway'])->name('destroy');
        });
    });
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/logout', [AdminAuthController::class, 'adminLogoutPage'])->name('logout.page');
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // Authenticated Admin Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Reservations Management
        Route::resource('reservations', AdminReservationController::class);

        // Steward and check-in/check-out routes
        Route::post('reservations/{reservation}/assign-steward', [AdminReservationController::class, 'assignSteward'])
        ->name('reservations.assign-steward');
        Route::post('reservations/{reservation}/check-in', [AdminReservationController::class, 'checkIn'])
        ->name('reservations.check-in');
        Route::post('reservations/{reservation}/check-out', [AdminReservationController::class, 'checkOut'])
        ->name('reservations.check-out');
        Route::get('/check-table-availability', [AdminReservationController::class, 'checkTableAvailability'])
        ->name('check-table-availability');
       

        // Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'adminIndex'])->name('index');
            
            // Reservation Orders
            Route::prefix('reservations/{reservation}')->name('reservations.')->group(function () {
                Route::get('/create', [OrderController::class, 'create'])->name('create');
                Route::post('/store', [OrderController::class, 'store'])->name('store');
                Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
                Route::put('/{order}', [OrderController::class, 'update'])->name('update');
                Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
                Route::post('/{order}/submit', [OrderController::class, 'submit'])->name('submit');
                Route::delete('/{order}/destroy', [OrderController::class, 'destroy'])->name('destroy');
            });

            // Takeaway Orders
            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/create', [OrderController::class, 'createAdminTakeaway'])->name('create');
                Route::post('/store', [OrderController::class, 'storeAdminTakeaway'])->name('store');
                Route::get('/{order}/edit', [OrderController::class, 'editAdminTakeaway'])->name('edit');
                Route::put('/{order}/update', [OrderController::class, 'updateAdminTakeaway'])->name('update');
                Route::get('/{order}/summary', [OrderController::class, 'summaryAdminTakeaway'])->name('summary');
                Route::post('/{order}/submit', [OrderController::class, 'submitAdminTakeaway'])->name('submit');
                Route::get('/{order}/show', [OrderController::class, 'showAdminTakeaway'])->name('show');
                Route::delete('/{order}/destroy', [OrderController::class, 'destroyAdminTakeaway'])->name('destroy');
            });
        });

        // Admin Order Routes
        Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
            // List all orders
            Route::get('/', [App\Http\Controllers\AdminOrderController::class, 'index'])->name('index');
            // Edit order status
            Route::get('{order}/edit', [App\Http\Controllers\AdminOrderController::class, 'edit'])->name('edit');
            Route::put('{order}', [App\Http\Controllers\AdminOrderController::class, 'update'])->name('update');
            // Branch orders
            Route::get('branch/{branch}', [App\Http\Controllers\AdminOrderController::class, 'branchOrders'])->name('branch');
            // Reservation Orders
            Route::prefix('reservations/{reservation}')->name('reservations.')->group(function () {
                Route::get('/create', [App\Http\Controllers\AdminOrderController::class, 'createForReservation'])->name('create');
                Route::post('/store', [App\Http\Controllers\AdminOrderController::class, 'storeForReservation'])->name('store');
                Route::get('/{order}/edit', [App\Http\Controllers\AdminOrderController::class, 'edit'])->name('edit');
                Route::put('/{order}', [App\Http\Controllers\AdminOrderController::class, 'update'])->name('update');
                Route::get('/{order}/summary', [App\Http\Controllers\AdminOrderController::class, 'summary'])->name('summary');
                Route::delete('/{order}/destroy', [App\Http\Controllers\AdminOrderController::class, 'destroy'])->name('destroy');
            });

            // Takeaway Orders
            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/create', [App\Http\Controllers\AdminOrderController::class, 'createTakeaway'])->name('create');
                Route::post('/store', [App\Http\Controllers\AdminOrderController::class, 'storeTakeaway'])->name('store');
                Route::get('/{order}/edit', [App\Http\Controllers\AdminOrderController::class, 'edit'])->name('edit');
                Route::put('/{order}/update', [App\Http\Controllers\AdminOrderController::class, 'update'])->name('update');
                Route::get('/{order}/summary', [App\Http\Controllers\AdminOrderController::class, 'summary'])->name('summary');
                Route::delete('/{order}/destroy', [App\Http\Controllers\AdminOrderController::class, 'destroy'])->name('destroy');
            });
        });

        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
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
                Route::get('/added-items', [ItemMasterController::class, 'added'])->name('added-items');
            });

            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
                // Route::get('/transactions', [ItemTransactionController::class, 'transactions'])->name('transactions');
                Route::get('/{transaction}', [ItemTransactionController::class, 'show'])->whereNumber('transaction')->name('show');
                Route::get('/{item_id}/{branch_id}/edit', [ItemTransactionController::class, 'edit'])->name('edit');
                Route::put('/{item_id}/{branch_id}', [ItemTransactionController::class, 'update'])->name('update');
                Route::delete('/{transaction}', [ItemTransactionController::class, 'destroy'])->name('destroy');

                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });
            // Categories
            Route::resource('categories', ItemCategoryController::class);
        });

        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::get('/{supplier}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('purchase-orders');
            Route::get('/{supplier}/grns', [SupplierController::class, 'goodsReceived'])->name('grns');
        });


        Route::get('/testpage', function () {return view('admin.testpage');})->name('testpage');
        Route::get('/reports', function () {return view('admin.reports.index');})->name('reports.index');
        Route::get('/customers', function () {return view('admin.customers.index');})->name('customers.index');
        Route::get('/web-test', function () {return view('admin.testpage');})->name('web-test.index');
        Route::get('/digital-menu', function () {return view('admin.digital-menu.index');})->name('digital-menu.index');
        Route::get('/settings', function () {return view('admin.settings.index');})->name('settings.index');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');
        
    });
});

Route::get('/test-email', function() {
    $reservation = \App\Models\Reservation::first();
    Mail::to('test@example.com')->send(new \App\Mail\ReservationConfirmed($reservation));
    return 'Email sent!';
});
Route::post('admin/orders/update-cart', [App\Http\Controllers\AdminOrderController::class, 'updateCart'])
    ->name('admin.orders.update-cart');

// Customer order store route (for /orders/create form)
Route::prefix('orders')->name('orders.')->group(function () {
    // ... existing routes ...
    Route::post('/store', [OrderController::class, 'store'])->name('store');
    Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
    Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
    Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    Route::put('/{order}', [OrderController::class, 'update'])->name('update');
});

// Admin Reservation Orders
Route::prefix('admin/orders/reservations/{reservation}')->name('admin.orders.reservations.')->group(function () {
    Route::get('/create', [\App\Http\Controllers\AdminOrderController::class, 'createForReservation'])->name('create');
    Route::post('/store', [\App\Http\Controllers\AdminOrderController::class, 'storeForReservation'])->name('store');
    Route::get('/{order}/summary', [\App\Http\Controllers\AdminOrderController::class, 'summary'])->name('summary');
});

// Admin Takeaway Orders
Route::prefix('admin/orders/takeaway')->name('admin.orders.takeaway.')->group(function () {
    Route::post('/store', [\App\Http\Controllers\Admin\OrderController::class, 'storeAdminTakeaway'])
        ->name('store');
});


