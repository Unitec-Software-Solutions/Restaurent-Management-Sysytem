<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\GrnDashboardController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemTransactionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierController;

use App\Http\Controllers\AdminOrderController;

use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GrnPaymentController;


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
        Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/all', [OrderController::class, 'allOrders'])->name('all');
        Route::post('/update-cart', [OrderController::class, 'updateCart'])->name('update-cart');
        Route::get('/create', [OrderController::class, 'create'])->name('create');

        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        
        // Takeaway Orders
        Route::prefix('takeaway')->name('takeaway.')->group(function () {
            Route::get('/create', [OrderController::class, 'createTakeaway'])->name('create');
            Route::post('/store', [OrderController::class, 'storeTakeaway'])->name('store');
            Route::get('/{order}/edit', [OrderController::class, 'editTakeaway'])->name('edit');
            Route::get('/{order}/summary', [OrderController::class, 'summary'])->name('summary');
            Route::delete('/{order}/delete', [OrderController::class, 'destroyTakeaway'])->name('destroy');
            // Add missing update and submit routes for customer takeaway orders
            Route::put('/{order}', [OrderController::class, 'updateTakeaway'])->name('update');
            Route::post('/{order}/submit', [OrderController::class, 'submitTakeaway'])->name('submit');
            Route::get('/{order}', [OrderController::class, 'showTakeaway'])->name('show');
        });
    });
});

Route::get('/login', function () { return redirect()->route('admin.login'); })->name('login');  // fix for redirecting to admin login Login not Found issue
// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    // Route::get('/logout', [AdminAuthController::class, 'adminLogoutPage'])->name('logout.page'); // replaced by
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // Authenticated Admin Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Reservations Management
        Route::resource('reservations', AdminReservationController::class);
        Route::post('reservations/{reservation}/assign-steward', [AdminReservationController::class, 'assignSteward'])
            ->name('reservations.assign-steward');
        Route::post('reservations/{reservation}/check-in', [AdminReservationController::class, 'checkIn'])
            ->name('reservations.check-in');
        Route::post('reservations/{reservation}/check-out', [AdminReservationController::class, 'checkOut'])
            ->name('reservations.check-out');
        Route::get('/check-table-availability', [AdminReservationController::class, 'checkTableAvailability'])
            ->name('check-table-availability');

        // Consolidated Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {

            // Order Dashboard and Index
            Route::get('/dashboard', [AdminOrderController::class, 'dashboard'])->name('dashboard');
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('reservations', [AdminOrderController::class, 'reservationIndex'])->name('reservations.index');
            Route::get('branch/{branch}', [AdminOrderController::class, 'branchOrders'])->name('branch');
            Route::post('/update-cart', [AdminOrderController::class, 'updateCart'])->name('update-cart');

            // Order CRUD Operations
            Route::get('{order}/edit', [AdminOrderController::class, 'edit'])->name('edit');
            Route::put('{order}', [AdminOrderController::class, 'update'])->name('update');
            // Reservation Order Summary (fix binding order)
            Route::get('/{order}/summary', [AdminOrderController::class, 'summary'])->name('summary');
            Route::delete('/{order}/destroy', [AdminOrderController::class, 'destroy'])->name('destroy');

            // Reservation Orders
            Route::prefix('reservations/{reservation}')->name('reservations.')->group(function () {
                Route::get('/create', [AdminOrderController::class, 'createForReservation'])->name('create');
                Route::post('/store', [AdminOrderController::class, 'storeForReservation'])->name('store');
                Route::get('/edit', [AdminOrderController::class, 'editReservationOrder'])->name('edit');
                Route::put('/update', [AdminOrderController::class, 'updateReservationOrder'])->name('update');
                Route::get('/{order}/summary', [AdminOrderController::class, 'summary'])->name('summary');
            });

            // Takeaway Orders
            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/', [AdminOrderController::class, 'takeawayIndex'])->name('index');
                Route::get('/create', [AdminOrderController::class, 'createTakeaway'])->name('create');
                Route::post('/store', [AdminOrderController::class, 'storeTakeaway'])->name('store');
                Route::get('/{order}/show', [OrderController::class, 'showTakeaway'])->name('takeaway.show');
                // Added missing edit and update routes for takeaway orders
                Route::get('/{order}/edit', [AdminOrderController::class, 'editTakeaway'])->name('edit');
                Route::put('/{order}', [AdminOrderController::class, 'updateTakeaway'])->name('update');
                // Add missing summary route for admin takeaway orders
                Route::get('/{order}/summary', [AdminOrderController::class, 'takeawaySummary'])->name('summary');
            });
        });

        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

            // Inventory Items
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

            // Stock Management
            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
                Route::get('/{transaction}', [ItemTransactionController::class, 'show'])->whereNumber('transaction')->name('show');
                Route::get('/{item_id}/{branch_id}/edit', [ItemTransactionController::class, 'edit'])->name('edit');
                Route::put('/{item_id}/{branch_id}', [ItemTransactionController::class, 'update'])->name('update');
                Route::delete('/{transaction}', [ItemTransactionController::class, 'destroy'])->name('destroy');

                // Stock Transactions
                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });
            
            // Categories
            Route::resource('categories', ItemCategoryController::class);
        });

        // Suppliers Management
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            // Supplier Routes
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::get('/{supplier}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('purchase-orders');
            Route::get('/{supplier}/grns', [SupplierController::class, 'goodsReceived'])->name('grns');

            //  Supplier json (remove  later | only for testing) 
            Route::get('/{supplier}/pending-grns', [SupplierController::class, 'pendingGrns']); 
            Route::get('/{supplier}/pending-pos', [SupplierController::class, 'pendingPos']);
        });

        // Separate GRN Routes
        Route::prefix('grn')->name('grn.')->group(function () {
            Route::get('/', [GrnDashboardController::class, 'index'])->name('index');
            Route::get('/create', [GrnDashboardController::class, 'create'])->name('create');
            Route::post('/', [GrnDashboardController::class, 'store'])->name('store');
            Route::get('/{grn}', [GrnDashboardController::class, 'show'])->name('show');
            Route::get('/{grn}/edit', [GrnDashboardController::class, 'edit'])->name('edit');
            Route::put('/{grn}', [GrnDashboardController::class, 'update'])->name('update');
            Route::post('/{grn}/verify', [GrnDashboardController::class, 'verify'])->name('verify');
            Route::get('/statistics/data', [GrnDashboardController::class, 'statistics'])->name('statistics');
        });

        // Supplier Payments ( temporarily moved out from suppliers section due to conflict with supplier routes )
        Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
                Route::get('/create', [SupplierPaymentController::class, 'create'])->name('create');
                Route::post('/', [SupplierPaymentController::class, 'store'])->name('store');
                Route::get('/{payment}', [SupplierPaymentController::class, 'show'])->name('show');
                Route::get('/{payment}/edit', [SupplierPaymentController::class, 'edit'])->name('edit');
                Route::put('/{payment}', [SupplierPaymentController::class, 'update'])->name('update');
                Route::delete('/{payment}', [SupplierPaymentController::class, 'destroy'])->name('destroy');
                Route::get('/{payment}/print', [SupplierPaymentController::class, 'print'])->name('print');
        });


        // Miscellaneous Admin Routes
        Route::get('/testpage', function () { return view('admin.testpage'); })->name('testpage');
        Route::get('/reports', function () { return view('admin.reports.index'); })->name('reports.index');
        Route::get('/customers', function () { return view('admin.customers.index'); })->name('customers.index');
        Route::get('/web-test', function () { return view('admin.testpage'); })->name('web-test.index');
        Route::get('/digital-menu', function () { return view('admin.digital-menu.index'); })->name('digital-menu.index');
        Route::get('/settings', function () { return view('admin.settings.index'); })->name('settings.index');

        // purchase orders ( temporarily moved out from suppliers section due to conflict with supplier routes )
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{po}', [PurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{po}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
            Route::post('/{po}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
            Route::get('/{po}/print', [PurchaseOrderController::class, 'print'])->name('print');

        });



        Route::get('/testpage', function () {
            return view('admin.testpage');
        })->name('testpage');
        Route::get('/reports', function () {
            return view('admin.reports.index');
        })->name('reports.index');
        Route::get('/customers', function () {
            return view('admin.customers.index');
        })->name('customers.index');

        Route::get('/digital-menu', function () {
            return view('admin.digital-menu.index');
        })->name('digital-menu.index');
        Route::get('/settings', function () {
            return view('admin.settings.index');
        })->name('settings.index');

        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');
    });

});

// Test Email Route
Route::get('/test-email', function() {
    $reservation = \App\Models\Reservation::first();
    Mail::to('test@example.com')->send(new \App\Mail\ReservationConfirmed($reservation));
    return 'Email sent!';

});

// Add a dedicated route for cancellation success and update the show route to enforce numeric ID constraints.
Route::get('/reservations/cancellation/success', [ReservationController::class, 'cancellationSuccess'])->name('reservations.cancellation.success');
