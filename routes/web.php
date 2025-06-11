<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController,
    CustomerDashboardController,
    ReservationController,
    AdminReservationController,
    AdminController,
    AdminAuthController,
    GrnDashboardController,
    ItemDashboardController,
    ItemCategoryController,
    ItemMasterController,
    ItemTransactionController,
    OrderController,
    SupplierController,
    AdminOrderController,
    SupplierPaymentController,
    PurchaseOrderController,
    GrnPaymentController,
    OrganizationController,
    ActivationController,
    RoleController,
    BranchController,
    SubscriptionController,
    UserController
};


/*-------------------------------------------------------------------------
| Public Routes
|------------------------------------------------------------------------*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*-------------------------------------------------------------------------
| Customer Routes
|------------------------------------------------------------------------*/
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
        Route::get('/{reservation}', [ReservationController::class, 'show'])->whereNumber('reservation')->name('show');
        Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/all', [OrderController::class, 'allOrders'])->name('all');
        Route::post('/update-cart', [OrderController::class, 'updateCart'])->name('update-cart');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}/summary', [OrderController::class, 'summary'])->whereNumber('order')->name('summary');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->whereNumber('order')->name('edit');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->whereNumber('order')->name('destroy');
        Route::put('/{order}', [OrderController::class, 'update'])->whereNumber('order')->name('update');
        
        // Takeaway Orders
        Route::prefix('takeaway')->name('takeaway.')->group(function () {
            Route::get('/create', [OrderController::class, 'createTakeaway'])->name('create');
            Route::post('/store', [OrderController::class, 'storeTakeaway'])->name('store');
            Route::get('/{order}/edit', [OrderController::class, 'editTakeaway'])->whereNumber('order')->name('edit');
            Route::get('/{order}/summary', [OrderController::class, 'summary'])->whereNumber('order')->name('summary');
            Route::delete('/{order}/delete', [OrderController::class, 'destroyTakeaway'])->whereNumber('order')->name('destroy');
            Route::put('/{order}', [OrderController::class, 'updateTakeaway'])->whereNumber('order')->name('update');
            Route::post('/{order}/submit', [OrderController::class, 'submitTakeaway'])->whereNumber('order')->name('submit');
            Route::get('/{order}', [OrderController::class, 'showTakeaway'])->whereNumber('order')->name('show');
        });
    });
});

/*-------------------------------------------------------------------------
| Authentication Routes
|------------------------------------------------------------------------*/
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);

/*-------------------------------------------------------------------------
| Admin Routes
|------------------------------------------------------------------------*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // Authenticated Admin Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Reservations Management
        Route::resource('reservations', AdminReservationController::class)->except(['create', 'store']);
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
            Route::get('/dashboard', [AdminOrderController::class, 'dashboard'])->name('dashboard');
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('reservations', [AdminOrderController::class, 'reservationIndex'])->name('reservations.index');
            Route::get('branch/{branch}', [AdminOrderController::class, 'branchOrders'])->whereNumber('branch')->name('branch');
            Route::post('/update-cart', [AdminOrderController::class, 'updateCart'])->name('update-cart');
            Route::get('{order}/edit', [AdminOrderController::class, 'edit'])->whereNumber('order')->name('edit');
            Route::put('{order}', [AdminOrderController::class, 'update'])->whereNumber('order')->name('update');
            Route::get('/{order}/summary', [AdminOrderController::class, 'summary'])->whereNumber('order')->name('summary');
            Route::delete('/{order}/destroy', [AdminOrderController::class, 'destroy'])->whereNumber('order')->name('destroy');

            // Reservation Orders
            Route::prefix('reservations/{reservation}')->name('reservations.')->group(function () {
                Route::get('/create', [AdminOrderController::class, 'createForReservation'])->name('create');
                Route::post('/store', [AdminOrderController::class, 'storeForReservation'])->name('store');
                Route::get('/edit', [AdminOrderController::class, 'editReservationOrder'])->name('edit');
                Route::put('/update', [AdminOrderController::class, 'updateReservationOrder'])->name('update');
                Route::get('/{order}/summary', [AdminOrderController::class, 'summary'])->whereNumber('order')->name('summary');
            });

            // Takeaway Orders
            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/', [AdminOrderController::class, 'takeawayIndex'])->name('index');
                Route::get('/create', [AdminOrderController::class, 'createTakeaway'])->name('create');
                Route::post('/store', [AdminOrderController::class, 'storeTakeaway'])->name('store');
                Route::get('/{order}/show', [OrderController::class, 'showTakeaway'])->whereNumber('order')->name('takeaway.show');
                Route::get('/{order}/edit', [AdminOrderController::class, 'editTakeaway'])->whereNumber('order')->name('edit');
                Route::put('/{order}', [AdminOrderController::class, 'updateTakeaway'])->whereNumber('order')->name('update');
                Route::get('/{order}/summary', [AdminOrderController::class, 'takeawaySummary'])->whereNumber('order')->name('summary');
            });
        });

        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

            // Items
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', [ItemMasterController::class, 'index'])->name('index');
                Route::get('/create', [ItemMasterController::class, 'create'])->name('create');
                Route::post('/', [ItemMasterController::class, 'store'])->name('store');
                Route::get('/{item}', [ItemMasterController::class, 'show'])->whereNumber('item')->name('show');
                Route::get('/{item}/edit', [ItemMasterController::class, 'edit'])->whereNumber('item')->name('edit');
                Route::put('/{item}', [ItemMasterController::class, 'update'])->whereNumber('item')->name('update');
                Route::delete('/{item}', [ItemMasterController::class, 'destroy'])->whereNumber('item')->name('destroy');
                Route::get('/create-template/{index}', [ItemMasterController::class, 'getItemFormPartial'])->name('form-partial');
                Route::get('/added-items', [ItemMasterController::class, 'added'])->name('added-items');
            });

            // Stock
            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
                Route::get('/{transaction}', [ItemTransactionController::class, 'show'])->whereNumber('transaction')->name('show');
                Route::get('/{item_id}/{branch_id}/edit', [ItemTransactionController::class, 'edit'])->whereNumber(['item_id', 'branch_id'])->name('edit');
                Route::put('/{item_id}/{branch_id}', [ItemTransactionController::class, 'update'])->whereNumber(['item_id', 'branch_id'])->name('update');
                Route::delete('/{transaction}', [ItemTransactionController::class, 'destroy'])->whereNumber('transaction')->name('destroy');

                // Transactions
                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });
            
            // Categories
            Route::resource('categories', ItemCategoryController::class);
        });

        // Suppliers Management
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->whereNumber('supplier')->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->whereNumber('supplier')->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->whereNumber('supplier')->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->whereNumber('supplier')->name('destroy');
            Route::get('/{supplier}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->whereNumber('supplier')->name('purchase-orders');
            Route::get('/{supplier}/grns', [SupplierController::class, 'goodsReceived'])->whereNumber('supplier')->name('grns');
        });

        // GRN Management
        Route::prefix('grn')->name('grn.')->group(function () {
            Route::get('/', [GrnDashboardController::class, 'index'])->name('index');
            Route::get('/create', [GrnDashboardController::class, 'create'])->name('create');
            Route::post('/', [GrnDashboardController::class, 'store'])->name('store');
            Route::get('/{grn}', [GrnDashboardController::class, 'show'])->whereNumber('grn')->name('show');
            Route::get('/{grn}/edit', [GrnDashboardController::class, 'edit'])->whereNumber('grn')->name('edit');
            Route::put('/{grn}', [GrnDashboardController::class, 'update'])->whereNumber('grn')->name('update');
            Route::post('/{grn}/verify', [GrnDashboardController::class, 'verify'])->whereNumber('grn')->name('verify');
            Route::get('/statistics/data', [GrnDashboardController::class, 'statistics'])->name('statistics');
            Route::get('/{grn}/print', [GrnDashboardController::class, 'print'])->whereNumber('grn')->name('print');
        });

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
            Route::get('/create', [SupplierPaymentController::class, 'create'])->name('create');
            Route::post('/', [SupplierPaymentController::class, 'store'])->name('store');
            Route::get('/{payment}', [SupplierPaymentController::class, 'show'])->whereNumber('payment')->name('show');
            Route::get('/{payment}/edit', [SupplierPaymentController::class, 'edit'])->whereNumber('payment')->name('edit');
            Route::put('/{payment}', [SupplierPaymentController::class, 'update'])->whereNumber('payment')->name('update');
            Route::delete('/{payment}', [SupplierPaymentController::class, 'destroy'])->whereNumber('payment')->name('destroy');
            Route::get('/{payment}/print', [SupplierPaymentController::class, 'print'])->whereNumber('payment')->name('print');
        });

        // Purchase Orders
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{po}', [PurchaseOrderController::class, 'show'])->whereNumber('po')->name('show');
            Route::get('/{po}/edit', [PurchaseOrderController::class, 'edit'])->whereNumber('po')->name('edit');
            Route::post('/{po}/approve', [PurchaseOrderController::class, 'approve'])->whereNumber('po')->name('approve');
            Route::get('/{id}/print', [PurchaseOrderController::class, 'print'])->whereNumber('id')->name('print');
        });

        // Additional Admin Routes
        Route::get('/testpage', function () { return view('admin.testpage'); })->name('testpage');
        Route::get('/reports', function () { return view('admin.reports.index'); })->name('reports.index');
        Route::get('/customers', function () { return view('admin.customers.index'); })->name('customers.index');
        Route::get('/digital-menu', function () { return view('admin.digital-menu.index'); })->name('digital-menu.index');
        Route::get('/settings', function () { return view('admin.settings.index'); })->name('settings.index');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');
    });
});

/*-------------------------------------------------------------------------
| Super Admin Routes (Organization Management)
|------------------------------------------------------------------------*/
Route::middleware(['auth:admin', 'can:create,App\Models\Organization'])->group(function () {
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
});

/*-------------------------------------------------------------------------
| Role Management Routes
|------------------------------------------------------------------------*/
Route::middleware(['auth:admin', 'organization.active', 'branch.active'])->group(function () {
    Route::post('/roles', [RoleController::class, 'store'])->can('create', App\Models\Role::class)->name('roles.store');
});

/*-------------------------------------------------------------------------
| Additional Routes (Legacy/Redirects)
|------------------------------------------------------------------------*/
Route::get('/reservations/cancellation/success', [ReservationController::class, 'cancellationSuccess'])->name('reservations.cancellation.success');

// Ensure all numeric parameters are properly constrained
Route::pattern('id', '[0-9]+');
Route::pattern('reservation', '[0-9]+');
Route::pattern('order', '[0-9]+');
Route::pattern('item', '[0-9]+');
Route::pattern('transaction', '[0-9]+');
Route::pattern('supplier', '[0-9]+');
Route::pattern('grn', '[0-9]+');
Route::pattern('payment', '[0-9]+');
Route::pattern('po', '[0-9]+');
Route::pattern('branch', '[0-9]+');
Route::pattern('item_id', '[0-9]+');
Route::pattern('branch_id', '[0-9]+');

Route::middleware(['auth:admin'])->group(function () {
    Route::resource('organizations', OrganizationController::class);
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('subscriptions/check', [SubscriptionController::class, 'checkSubscriptions']);
});