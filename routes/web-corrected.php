<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import all controllers
use App\Http\Controllers\{
    CustomerDashboardController,
    ReservationController,
    AdminReservationController,
    AdminController,
    AdminAuthController,
    AdminAuthTestController,
    GrnDashboardController,
    ItemDashboardController,
    ItemMasterController,
    ItemTransactionController,
    OrderController,
    SupplierController,
    SupplierPaymentController,
    PurchaseOrderController,
    AdminOrderController,
    OrganizationController,
    RoleController,
    BranchController,
    UserController,
    GoodsTransferNoteController,
    RealtimeDashboardController,
    AdminTestPageController,
    DatabaseTestController,
    ProductionRequestsMasterController,
    ProductionOrderController,
    ProductionRequestItemController,
    ProductionSessionController,
    ProductionController,
    ProductionRecipeController,
};

use App\Http\Controllers\Admin\{
    MenuController,
    SubscriptionPlanController,
    PaymentController,
    CustomerController,
    SettingController,
    ReportController,
    DebugController,
    EmployeeController,
    GrnController,
    InventoryController,
    OrderController as AdminOrderControllerAdmin,
    PurchaseOrderController as AdminPurchaseOrderController,
    SupplierController as AdminSupplierController,
    BillController,
    CheckTableAvailabilityController,
    ReservationController as AdminReservationControllerAdmin,
};

use App\Http\Controllers\Guest\GuestController;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\SuperAdmin;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::prefix('guest')->name('guest.')->group(function () {
    // Menu browsing
    Route::get('/menu/branches', [GuestController::class, 'viewMenu'])->name('menu.branch-selection');
    Route::get('/menu/{branchId?}', [GuestController::class, 'viewMenu'])->name('menu.view');
    Route::get('/menu/{branchId}/date/{date}', [GuestController::class, 'viewMenuByDate'])->name('menu.date');
    Route::get('/menu/{branchId}/special', [GuestController::class, 'viewSpecialMenu'])->name('menu.special');

    // Cart management
    Route::post('/cart/add', [GuestController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update', [GuestController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/remove/{itemId}', [GuestController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/cart', [GuestController::class, 'viewCart'])->name('cart.view');
    Route::delete('/cart/clear', [GuestController::class, 'clearCart'])->name('cart.clear');

    // Order management
    Route::post('/order/create', [GuestController::class, 'createOrder'])->name('order.create');
    Route::get('/order/{orderId}/confirmation/{token}', [GuestController::class, 'orderConfirmation'])->name('order.confirmation');
    Route::get('/order/{orderNumber}/track', [GuestController::class, 'trackOrder'])->name('order.track');
    Route::get('/order/{orderNumber}/details', [GuestController::class, 'orderDetails'])->name('order.details');

    // Reservations
    Route::get('/reservations/create/{branchId?}', [GuestController::class, 'showReservationForm'])->name('reservations.create');
    Route::post('/reservations/store', [GuestController::class, 'createReservation'])->name('reservations.store');
    Route::get('/reservations/{confirmationNumber}/confirmation', [GuestController::class, 'reservationConfirmation'])->name('reservations.confirmation');
    Route::get('/reservations/{reservationId}/confirmation/{token}', [GuestController::class, 'reservationConfirmationById'])->name('reservation.confirmation');

    // Guest session management
    Route::get('/session/info', [GuestController::class, 'sessionInfo'])->name('session.info');
});

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

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

        // Stock checking
        Route::post('/check-stock', [OrderController::class, 'checkStock'])->name('check-stock');
        Route::post('/{order}/print-kot', [OrderController::class, 'printKOT'])->name('print-kot');
        Route::post('/{order}/print-bill', [OrderController::class, 'printBill'])->name('print-bill');
        Route::post('/{order}/mark-preparing', [OrderController::class, 'markAsPreparing'])->name('mark-preparing');
        Route::post('/{order}/mark-ready', [OrderController::class, 'markAsReady'])->name('mark-ready');
        Route::post('/{order}/complete', [OrderController::class, 'completeOrder'])->name('complete');

        // Takeaway Orders
        Route::prefix('takeaway')->name('takeaway.')->group(function () {
            Route::get('/', [OrderController::class, 'indexTakeaway'])->name('index');
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

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication routes (no middleware)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // All authenticated admin routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');

        // Test page route (development only)
        Route::get('/testpage', [AdminTestPageController::class, 'index'])->name('testpage');

        // Reservations Management
        Route::resource('reservations', AdminReservationController::class);

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
            });

            // Stock Management
            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');

                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });

            // GTN Management
            Route::prefix('gtn')->name('gtn.')->group(function () {
                Route::get('/search-items', [GoodsTransferNoteController::class, 'searchItems'])->name('search-items');
                Route::get('/item-stock', [GoodsTransferNoteController::class, 'getItemStock'])->name('item-stock');
                Route::get('/', [GoodsTransferNoteController::class, 'index'])->name('index');
                Route::get('/create', [GoodsTransferNoteController::class, 'create'])->name('create');
                Route::post('/', [GoodsTransferNoteController::class, 'store'])->name('store');
                Route::get('/{gtn}', [GoodsTransferNoteController::class, 'show'])->whereNumber('gtn')->name('show');
            });
        });

        // Suppliers Management
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');

            // JSON endpoints for testing
            Route::get('/{supplier}/pending-grns', [SupplierController::class, 'pendingGrns']);
            Route::get('/{supplier}/pending-pos', [SupplierController::class, 'pendingPos']);
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
            Route::get('/{grn}/print', [GrnDashboardController::class, 'print'])->name('print');
        });

        // Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/create', [AdminOrderController::class, 'create'])->name('create');
            Route::post('/', [AdminOrderController::class, 'store'])->name('store');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->whereNumber('order')->name('show');
            Route::get('/{order}/edit', [AdminOrderController::class, 'edit'])->whereNumber('order')->name('edit');
            Route::put('/{order}', [AdminOrderController::class, 'update'])->whereNumber('order')->name('update');
            Route::delete('/{order}', [AdminOrderController::class, 'destroy'])->whereNumber('order')->name('destroy');

            // Enhanced order management
            Route::get('/enhanced-create', [AdminOrderController::class, 'enhancedCreate'])->name('enhanced-create');
            Route::post('/enhanced-store', [AdminOrderController::class, 'enhancedStore'])->name('enhanced-store');
            Route::post('/{order}/confirm-stock', [AdminOrderController::class, 'confirmOrderStock'])->name('confirm-stock');
            Route::delete('/{order}/cancel-with-stock', [AdminOrderController::class, 'cancelOrderWithStock'])->name('cancel-with-stock');

            // Takeaway Orders
            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/', [AdminOrderController::class, 'indexTakeaway'])->name('index');
                Route::get('/create', function() {
                    return redirect()->route('admin.orders.create', ['type' => 'takeaway']);
                })->name('create');
                Route::post('/', [AdminOrderController::class, 'storeTakeaway'])->name('store');
                Route::get('/{order}', [AdminOrderController::class, 'showTakeaway'])->whereNumber('order')->name('show');
                Route::get('/{order}/edit', [AdminOrderController::class, 'editTakeaway'])->whereNumber('order')->name('edit');
                Route::put('/{order}', [AdminOrderController::class, 'updateTakeaway'])->whereNumber('order')->name('update');
                Route::delete('/{order}', [AdminOrderController::class, 'destroyTakeaway'])->whereNumber('order')->name('destroy');
            });
        });

        // Menu Management
        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::get('/list', [MenuController::class, 'list'])->name('list');
            Route::get('/create', [MenuController::class, 'create'])->name('create');
            Route::post('/store', [MenuController::class, 'store'])->name('store');
            Route::get('/bulk-create', [MenuController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-store', [MenuController::class, 'bulkStore'])->name('bulk-store');
            Route::get('/calendar', [MenuController::class, 'calendar'])->name('calendar');
            Route::get('/calendar/data', [MenuController::class, 'getCalendarData'])->name('calendar.data');
            Route::get('/{menu}/show', [MenuController::class, 'show'])->name('show');
            Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
            Route::put('/{menu}/update', [MenuController::class, 'update'])->name('update');
            Route::get('/{menu}/preview', [MenuController::class, 'preview'])->name('preview');
            Route::post('/{menu}/activate', [MenuController::class, 'activate'])->name('activate');
            Route::post('/{menu}/deactivate', [MenuController::class, 'deactivate'])->name('deactivate');
            Route::post('/bulk-activate', [MenuController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [MenuController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
            Route::get('/safety-dashboard', [MenuController::class, 'safetyDashboard'])->name('safety-dashboard');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', SuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Organizations CRUD
    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::get('organizations/{organization}/summary', [OrganizationController::class, 'summary'])->name('organizations.summary');
    Route::put('organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->name('organizations.regenerate-key');

    // Branches
    Route::prefix('organizations/{organization}')->group(function () {
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Global Branches Index
    Route::get('branches', [BranchController::class, 'globalIndex'])->name('branches.global');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/assign-role', [UserController::class, 'assignRoleForm'])->name('users.assign-role');
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Subscription Plans
    Route::resource('subscription-plans', SubscriptionPlanController::class);
});

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/

Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/create', [PaymentController::class, 'create'])->name('create');
    Route::get('/process', [PaymentController::class, 'process'])->name('process');
});
