<?php

use Illuminate\Support\Facades\Route;
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
    AdminOrderController,
    OrganizationController,
    RoleController,
    BranchController,
    UserController,
    GoodsTransferNoteController,
    RealtimeDashboardController,
    AdminTestPageController,
    DatabaseTestController,
};
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Middleware\SuperAdmin;


/*-------------------------------------------------------------------------
| Debug Routes - Removed in production refactoring
|------------------------------------------------------------------------*/
// Debug routes have been removed for production readiness

/*-------------------------------------------------------------------------
| Public Routes
|------------------------------------------------------------------------*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*-------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|------------------------------------------------------------------------*/
Route::prefix('guest')->name('guest.')->group(function () {
    // Menu browsing
    Route::get('/menu/branches', [\App\Http\Controllers\Guest\GuestController::class, 'viewMenu'])->name('menu.branch-selection');
    Route::get('/menu/{branchId?}', [\App\Http\Controllers\Guest\GuestController::class, 'viewMenu'])->name('menu.view');
    Route::get('/menu/{branchId}/date/{date}', [\App\Http\Controllers\Guest\GuestController::class, 'viewMenuByDate'])->name('menu.date');
    Route::get('/menu/{branchId}/special', [\App\Http\Controllers\Guest\GuestController::class, 'viewSpecialMenu'])->name('menu.special');
    
    // Cart management
    Route::post('/cart/add', [\App\Http\Controllers\Guest\GuestController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update', [\App\Http\Controllers\Guest\GuestController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/remove/{itemId}', [\App\Http\Controllers\Guest\GuestController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/cart', [\App\Http\Controllers\Guest\GuestController::class, 'viewCart'])->name('cart.view');
    Route::delete('/cart/clear', [\App\Http\Controllers\Guest\GuestController::class, 'clearCart'])->name('cart.clear');
    
    // Order management
    Route::post('/order/create', [\App\Http\Controllers\Guest\GuestController::class, 'createOrder'])->name('order.create');
    Route::get('/order/{orderId}/confirmation/{token}', [\App\Http\Controllers\Guest\GuestController::class, 'orderConfirmation'])->name('order.confirmation');
    Route::get('/order/{orderNumber}/track', [\App\Http\Controllers\Guest\GuestController::class, 'trackOrder'])->name('order.track');
    Route::get('/order/{orderNumber}/details', [\App\Http\Controllers\Guest\GuestController::class, 'orderDetails'])->name('order.details');
    
    // Reservations
    Route::get('/reservations/create/{branchId?}', [\App\Http\Controllers\Guest\GuestController::class, 'showReservationForm'])->name('reservations.create');
    Route::post('/reservations/store', [\App\Http\Controllers\Guest\GuestController::class, 'createReservation'])->name('reservations.store');
    Route::get('/reservations/{confirmationNumber}/confirmation', [\App\Http\Controllers\Guest\GuestController::class, 'reservationConfirmation'])->name('reservations.confirmation');
    Route::get('/reservations/{reservationId}/confirmation/{token}', [\App\Http\Controllers\Guest\GuestController::class, 'reservationConfirmationById'])->name('reservation.confirmation');
    
    // Guest session management
    Route::get('/session/info', [\App\Http\Controllers\Guest\GuestController::class, 'sessionInfo'])->name('session.info');
});

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


/*-------------------------------------------------------------------------
| Authentication Routes
|------------------------------------------------------------------------*/
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);

/*-------------------------------------------------------------------------
| Debug Routes (Development Only)
|------------------------------------------------------------------------*/
if (config('app.debug')) {
    Route::get('/admin/auth/debug', [AdminAuthTestController::class, 'checkAuth'])->name('admin.auth.check');
    Route::get('/admin/auth/test', function() {
        return [
            'admin_authenticated' => \Illuminate\Support\Facades\Auth::guard('admin')->check(),
            'admin_user' => \Illuminate\Support\Facades\Auth::guard('admin')->user(),
            'session_id' => session()->getId(),
            'session_data' => session()->all()
        ];
    })->middleware('auth:admin');
    
    Route::get('/debug/session', function() {
        return [
            'session_driver' => config('session.driver'),
            'session_table' => config('session.table'),
            'session_connection' => config('session.connection'),
            'session_id' => session()->getId(),
            'session_name' => session()->getName(),
            'session_exists' => session()->isStarted(),
        ];
    });
}

/*-------------------------------------------------------------------------
| Admin Routes
|------------------------------------------------------------------------*/
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
        
        // Inventory Management - Remove duplicate middleware
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

        // Suppliers Management - Fix middleware conflict
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
            Route::get('/{grn}', [GrnDashboardController::class, 'show'])->name('show');
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
            
            // Takeaway Orders
            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/', [AdminOrderController::class, 'indexTakeaway'])->name('index');
                Route::get('/create', [AdminOrderController::class, 'createTakeaway'])->name('create');
                Route::post('/', [AdminOrderController::class, 'storeTakeaway'])->name('store');
                Route::get('/{order}', [AdminOrderController::class, 'showTakeaway'])->whereNumber('order')->name('show');
                Route::get('/{order}/edit', [AdminOrderController::class, 'editTakeaway'])->whereNumber('order')->name('edit');
                Route::put('/{order}', [AdminOrderController::class, 'updateTakeaway'])->whereNumber('order')->name('update');
                Route::delete('/{order}', [AdminOrderController::class, 'destroyTakeaway'])->whereNumber('order')->name('destroy');
            });
        });
    });
});

// Super Admin Routes
Route::middleware(['auth:admin', SuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Organizations CRUD
    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::get('organizations/{organization}/summary', [OrganizationController::class, 'summary'])->name('organizations.summary');
    Route::put('organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->name('organizations.regenerate-key');

    // Branches: Organization-specific CRUD
    Route::prefix('organizations/{organization}')->group(function () {
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        
        // Organization User creation
        Route::get('users/create', [UserController::class, 'create'])->name('organization.users.create');
        
        // Branch-specific User creation
        Route::get('branches/{branch}/users/create', [UserController::class, 'create'])->name('branch.users.create');
    });

    // Global Branches Index (for Super Admin to see all branches)
    Route::get('branches', [BranchController::class, 'globalIndex'])->name('branches.global');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');


    // Roles & Permissions
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

    // Subscription Plans
    Route::resource('subscription-plans', SubscriptionPlanController::class);
});

// Branch summary and regenerate key
Route::middleware(['auth:admin'])->group(function () {
    Route::get('branches/{branch}/summary', [BranchController::class, 'summary'])->name('branches.summary');
    Route::put('branches/{branch}/regenerate-key', [BranchController::class, 'regenerateKey'])->name('branches.regenerate-key');
});

// Show activation form for all admins
Route::get('admin/organizations/activate', [OrganizationController::class, 'showActivationForm'])->name('admin.organizations.activate.form');
Route::post('admin/organizations/activate', [OrganizationController::class, 'activateOrganization'])->name('admin.organizations.activate.submit');

Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('branches/activate', [BranchController::class, 'showActivationForm'])->name('branches.activate.form');
    Route::post('branches/activate', [BranchController::class, 'activateBranch'])->name('branches.activate.submit');
});

Route::middleware(['web', 'auth:admin', App\Http\Middleware\SuperAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Organizations CRUD
        Route::resource('organizations', OrganizationController::class)->except(['show']);
        Route::get('organizations/{organization}/summary', [OrganizationController::class, 'summary'])->name('organizations.summary');
        Route::put('organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->name('organizations.regenerate-key');

        // Branches: Organization-specific CRUD
        Route::prefix('organizations/{organization}')->group(function () {
            Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
            Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
            Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
            Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
            Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
            Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        });

        // Global Branches Index (for Super Admin to see all branches)
        Route::get('branches', [BranchController::class, 'globalIndex'])->name('branches.global');

        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');


        // Roles & Permissions
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

        // Subscription Plans
        Route::resource('subscription-plans', SubscriptionPlanController::class);

        Route::resource('subscriptions', \App\Http\Controllers\SubscriptionController::class)->only(['edit', 'update']);
    });

Route::middleware(['auth:admin'])->group(function () {
    // Organizations CRUD
    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::get('organizations/{organization}/summary', [OrganizationController::class, 'summary'])->name('organizations.summary');
    Route::put('organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->name('organizations.regenerate-key');

    // Branches: Organization-specific CRUD
    Route::prefix('organizations/{organization}')->group(function () {
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Global Branches Index (for Super Admin to see all branches)
    Route::get('branches', [BranchController::class, 'globalIndex'])->name('branches.global');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');


    // Roles & Permissions
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

    // Subscription Plans
    Route::resource('subscription-plans', SubscriptionPlanController::class);

});

Route::middleware(['auth:admin', SuperAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['show']);
        Route::resource('modules', \App\Http\Controllers\ModuleController::class)->except(['show']);
        Route::get('roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    });

Route::middleware(['auth:admin', App\Http\Middleware\SuperAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{user}/assign-role', [UserController::class, 'assignRoleForm'])->name('users.assign-role');
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
});

/*-------------------------------------------------------------------------
| Enhanced Order Management API Routes
|------------------------------------------------------------------------*/
Route::prefix('admin/api')->middleware(['auth:admin'])->group(function () {
    // Stock and availability APIs
    Route::get('/stock-summary', [AdminOrderController::class, 'getStockSummary']);
    Route::post('/validate-cart', [AdminOrderController::class, 'validateCart']);
    Route::get('/menu-alternatives/{item}', [AdminOrderController::class, 'getMenuAlternatives']);
    Route::get('/real-time-availability/{branch}', [AdminOrderController::class, 'getRealTimeAvailability']);
    
    // Product catalog APIs
    Route::get('/menu-items/{branch}', [AdminOrderController::class, 'getMenuItems']);
    Route::get('/inventory-items/{branch}', [AdminOrderController::class, 'getInventoryItems']);
    Route::post('/update-menu-availability/{branch}', [AdminOrderController::class, 'updateMenuAvailability']);
});

// Enhanced order routes
Route::prefix('admin/orders')->middleware(['auth:admin'])->group(function () {
    Route::get('/enhanced-create', [AdminOrderController::class, 'enhancedCreate'])->name('admin.orders.enhanced-create');
    Route::post('/enhanced-store', [AdminOrderController::class, 'enhancedStore'])->name('admin.orders.enhanced-store');
    Route::post('/{order}/confirm-stock', [AdminOrderController::class, 'confirmOrderStock'])->name('admin.orders.confirm-stock');
    Route::delete('/{order}/cancel-with-stock', [AdminOrderController::class, 'cancelOrderWithStock'])->name('admin.orders.cancel-with-stock');
});

/*-------------------------------------------------------------------------
| Real-time Dashboard Routes
|------------------------------------------------------------------------*/
Route::prefix('admin/dashboard')->middleware(['auth:admin'])->group(function () {
    Route::get('/realtime-inventory', [RealtimeDashboardController::class, 'index'])->name('admin.dashboard.realtime-inventory');
    
    // API endpoints for dashboard
    Route::get('/api/recent-orders', [RealtimeDashboardController::class, 'getRecentOrdersApi']);
    Route::get('/api/low-stock-items', [RealtimeDashboardController::class, 'getLowStockItemsApi']);
    Route::get('/api/stock-levels-chart', [RealtimeDashboardController::class, 'getStockLevelsChart']);
    Route::get('/api/menu-availability-stats', [RealtimeDashboardController::class, 'getMenuAvailabilityStatsApi']);
    Route::post('/api/export-stock-report', [RealtimeDashboardController::class, 'exportStockReport']);
    Route::get('/api/dashboard-alerts', [RealtimeDashboardController::class, 'getDashboardAlerts']);
    Route::get('/api/dashboard-summary', [RealtimeDashboardController::class, 'getDashboardSummary']);
});

// Menu safety dashboard
Route::get('menus/safety-dashboard', [MenuController::class, 'safetyDashboard'])->name('admin.menus.safety-dashboard');

// Database test operations
Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
    Route::post('/diagnose-table', [DatabaseTestController::class, 'diagnoseTable']);
    Route::post('/run-migrations', [DatabaseTestController::class, 'runMigrations']);
    Route::post('/run-seeder', [DatabaseTestController::class, 'runSeeder']);
    Route::post('/full-diagnose', [DatabaseTestController::class, 'fullDiagnose']);
    Route::post('/fresh-migrate', [DatabaseTestController::class, 'freshMigrate']);
    Route::post('/test-orders', [DatabaseTestController::class, 'testOrderCreation']);
    Route::get('/system-stats', [DatabaseTestController::class, 'getSystemStats']);
    Route::get('/order-stats', [DatabaseTestController::class, 'getOrderStats']);
    Route::get('/recent-orders', [DatabaseTestController::class, 'getRecentOrders']);
    Route::get('/orders-preview', [DatabaseTestController::class, 'getOrdersPreview']);
    
    // Payment Management
    Route::get('payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [App\Http\Controllers\Admin\PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::get('payments/{payment}/edit', [App\Http\Controllers\Admin\PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('payments/{payment}/print', [App\Http\Controllers\Admin\PaymentController::class, 'print'])->name('payments.print');
});

Route::get('customers/index', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->middleware(['auth:admin'])->name('admin.customers.index');
Route::get('digital-menu/index', [App\Http\Controllers\Admin\DigitalMenuController::class, 'index'])->middleware(['auth:admin'])->name('admin.digital-menu.index');
Route::get('settings/index', [App\Http\Controllers\Admin\SettingController::class, 'index'])->middleware(['auth:admin'])->name('admin.settings.index');
Route::get('reports/index', [App\Http\Controllers\Admin\ReportController::class, 'index'])->middleware(['auth:admin'])->name('admin.reports.index');
Route::get('debug/routes', [App\Http\Controllers\Admin\DebugController::class, 'routes'])->middleware(['auth:admin'])->name('admin.debug.routes');
Route::get('debug/routes/test', [App\Http\Controllers\Admin\DebugController::class, 'routes'])->middleware(['auth:admin'])->name('admin.debug.routes.test');
Route::get('debug/routes/generate', [App\Http\Controllers\Admin\DebugController::class, 'routes'])->middleware(['auth:admin'])->name('admin.debug.routes.generate');
Route::get('debug/routes/export', [App\Http\Controllers\Admin\DebugController::class, 'routes'])->middleware(['auth:admin'])->name('admin.debug.routes.export');
Route::get('employees', [App\Http\Controllers\Admin\EmployeeController::class, 'index'])->middleware(['auth:admin'])->name('admin.employees.index');
Route::get('employees/create', [App\Http\Controllers\Admin\EmployeeController::class, 'create'])->middleware(['auth:admin'])->name('admin.employees.create');
Route::post('employees', [App\Http\Controllers\Admin\EmployeeController::class, 'store'])->middleware(['auth:admin'])->name('admin.employees.store');
Route::get('employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'show'])->middleware(['auth:admin'])->name('admin.employees.show');
Route::get('employees/{employee}/edit', [App\Http\Controllers\Admin\EmployeeController::class, 'edit'])->middleware(['auth:admin'])->name('admin.employees.edit');
Route::put('employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'update'])->middleware(['auth:admin'])->name('admin.employees.update');
Route::delete('employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroy'])->middleware(['auth:admin'])->name('admin.employees.destroy');
Route::post('employees/{employee}/restore', [App\Http\Controllers\Admin\EmployeeController::class, 'restore'])->middleware(['auth:admin'])->name('admin.employees.restore');
Route::patch('employees/{employee}/availability', [App\Http\Controllers\Admin\EmployeeController::class, 'updateAvailability'])->middleware(['auth:admin'])->name('admin.employees.update-availability');
Route::get('grn/link-payment', [App\Http\Controllers\Admin\GrnController::class, 'linkPayment'])->middleware(['auth:admin'])->name('admin.grn.link-payment');

Route::get('inventory/gtn/items-with-stock', [App\Http\Controllers\Admin\InventoryController::class, 'gtn'])->middleware(['auth:admin'])->name('admin.inventory.gtn.items-with-stock');
Route::get('inventory/gtn/update', [App\Http\Controllers\Admin\InventoryController::class, 'gtn'])->middleware(['auth:admin'])->name('admin.inventory.gtn.update');
Route::get('inventory/gtn/print', [App\Http\Controllers\Admin\InventoryController::class, 'gtn'])->middleware(['auth:admin'])->name('admin.inventory.gtn.print');
Route::get('inventory/gtn/edit', [App\Http\Controllers\Admin\InventoryController::class, 'gtn'])->middleware(['auth:admin'])->name('admin.inventory.gtn.edit');
Route::get('inventory/items/restore', [App\Http\Controllers\Admin\InventoryController::class, 'items'])->middleware(['auth:admin'])->name('admin.inventory.items.restore');
Route::get('inventory/stock/update', [App\Http\Controllers\Admin\InventoryController::class, 'stock'])->middleware(['auth:admin'])->name('admin.inventory.stock.update');
Route::get('inventory/stock/edit', [App\Http\Controllers\Admin\InventoryController::class, 'stock'])->middleware(['auth:admin'])->name('admin.inventory.stock.edit');
Route::get('inventory/stock/show', [App\Http\Controllers\Admin\InventoryController::class, 'stock'])->middleware(['auth:admin'])->name('admin.inventory.stock.show');
// Menu routes - properly ordered to avoid conflicts
Route::get('menus/index', [App\Http\Controllers\Admin\MenuController::class, 'index'])->middleware(['auth:admin'])->name('admin.menus.index');
Route::get('menus/list', [App\Http\Controllers\Admin\MenuController::class, 'list'])->middleware(['auth:admin'])->name('admin.menus.list');
Route::get('menus/create', [App\Http\Controllers\Admin\MenuController::class, 'create'])->middleware(['auth:admin'])->name('admin.menus.create');
Route::post('menus/store', [App\Http\Controllers\Admin\MenuController::class, 'store'])->middleware(['auth:admin'])->name('admin.menus.store');
Route::get('menus/bulk-create', [App\Http\Controllers\Admin\MenuController::class, 'bulkCreate'])->middleware(['auth:admin'])->name('admin.menus.bulk-create');
Route::post('menus/bulk-store', [App\Http\Controllers\Admin\MenuController::class, 'bulkStore'])->middleware(['auth:admin'])->name('admin.menus.bulk-store');
Route::get('menus/calendar', [App\Http\Controllers\Admin\MenuController::class, 'calendar'])->middleware(['auth:admin'])->name('admin.menus.calendar');
Route::get('menus/calendar/data', [App\Http\Controllers\Admin\MenuController::class, 'getCalendarData'])->middleware(['auth:admin'])->name('admin.menus.calendar.data');
Route::get('menus/{menu}/show', [App\Http\Controllers\Admin\MenuController::class, 'show'])->middleware(['auth:admin'])->name('admin.menus.show');
Route::get('menus/{menu}/edit', [App\Http\Controllers\Admin\MenuController::class, 'edit'])->middleware(['auth:admin'])->name('admin.menus.edit');
Route::put('menus/{menu}/update', [App\Http\Controllers\Admin\MenuController::class, 'update'])->middleware(['auth:admin'])->name('admin.menus.update');
Route::get('menus/{menu}/preview', [App\Http\Controllers\Admin\MenuController::class, 'preview'])->middleware(['auth:admin'])->name('admin.menus.preview');
Route::post('menus/{menu}/activate', [App\Http\Controllers\Admin\MenuController::class, 'activate'])->middleware(['auth:admin'])->name('admin.menus.activate');
Route::post('menus/{menu}/deactivate', [App\Http\Controllers\Admin\MenuController::class, 'deactivate'])->middleware(['auth:admin'])->name('admin.menus.deactivate');
Route::post('menus/bulk-activate', [App\Http\Controllers\Admin\MenuController::class, 'bulkActivate'])->middleware(['auth:admin'])->name('admin.menus.bulk-activate');
Route::post('menus/bulk-deactivate', [App\Http\Controllers\Admin\MenuController::class, 'bulkDeactivate'])->middleware(['auth:admin'])->name('admin.menus.bulk-deactivate');
Route::delete('menus/{menu}', [App\Http\Controllers\Admin\MenuController::class, 'destroy'])->middleware(['auth:admin'])->name('admin.menus.destroy');
Route::get('orders/archive-old-menus', [App\Http\Controllers\Admin\OrderController::class, 'archive-old-menus'])->middleware(['auth:admin'])->name('admin.orders.archive-old-menus');
Route::get('orders/menu-safety-status', [App\Http\Controllers\Admin\OrderController::class, 'menu-safety-status'])->middleware(['auth:admin'])->name('admin.orders.menu-safety-status');
Route::get('orders/reservations/store', [App\Http\Controllers\Admin\OrderController::class, 'reservations'])->middleware(['auth:admin'])->name('admin.orders.reservations.store');
Route::get('orders/update-cart', [App\Http\Controllers\Admin\OrderController::class, 'update-cart'])->middleware(['auth:admin'])->name('admin.orders.update-cart');
Route::get('orders/reservations/index', [App\Http\Controllers\Admin\OrderController::class, 'reservations'])->middleware(['auth:admin'])->name('admin.orders.reservations.index');
Route::get('payments/process', [App\Http\Controllers\PaymentController::class, 'process'])->name('payments.process');
Route::get('orders/orders/reservations/edit', [App\Http\Controllers\Admin\OrderController::class, 'orders'])->middleware(['auth:admin'])->name('admin.orders.orders.reservations.edit');
Route::get('orders/payment', [App\Http\Controllers\OrderController::class, 'payment'])->name('orders.payment');
Route::get('orders/takeaway/branch', [App\Http\Controllers\Admin\OrderController::class, 'takeaway'])->middleware(['auth:admin'])->name('admin.orders.takeaway.branch');
Route::get('orders/dashboard', [App\Http\Controllers\Admin\OrderController::class, 'dashboard'])->middleware(['auth:admin'])->name('admin.orders.dashboard');
Route::get('check-table-availability', [App\Http\Controllers\Admin\CheckTableAvailabilityController::class, 'index'])->middleware(['auth:admin'])->name('admin.check-table-availability');
Route::get('orders/reservations/create', [App\Http\Controllers\Admin\OrderController::class, 'reservations'])->middleware(['auth:admin'])->name('admin.orders.reservations.create');
Route::get('orders/reservations/edit', [App\Http\Controllers\Admin\OrderController::class, 'reservations'])->middleware(['auth:admin'])->name('admin.orders.reservations.edit');
Route::get('reservations/assign-steward', [App\Http\Controllers\Admin\ReservationController::class, 'assignSteward'])->middleware(['auth:admin'])->name('admin.reservations.assign-steward');
Route::get('reservations/check-in', [App\Http\Controllers\Admin\ReservationController::class, 'checkIn'])->middleware(['auth:admin'])->name('admin.reservations.check-in');
Route::get('reservations/check-out', [App\Http\Controllers\Admin\ReservationController::class, 'checkOut'])->middleware(['auth:admin'])->name('admin.reservations.check-out');
Route::get('orders/orders/reservations/create', [App\Http\Controllers\Admin\OrderController::class, 'orders'])->middleware(['auth:admin'])->name('admin.orders.orders.reservations.create');
Route::get('roles/assign', [App\Http\Controllers\RoleController::class, 'assign'])->name('roles.assign');
Route::put('grn/update', [App\Http\Controllers\Admin\GrnController::class, 'update'])->middleware(['auth:admin'])->name('admin.grn.update');
Route::get('grn/print', [App\Http\Controllers\Admin\GrnController::class, 'print'])->middleware(['auth:admin'])->name('admin.grn.print');
Route::get('grn/edit', [App\Http\Controllers\Admin\GrnController::class, 'edit'])->middleware(['auth:admin'])->name('admin.grn.edit');
Route::get('grn/verify', [App\Http\Controllers\Admin\GrnController::class, 'verify'])->middleware(['auth:admin'])->name('admin.grn.verify');
Route::get('purchase-orders/show', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->middleware(['auth:admin'])->name('admin.purchase-orders.show');
Route::get('purchase-orders/index', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->middleware(['auth:admin'])->name('admin.purchase-orders.index');
Route::post('purchase-orders/store', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->middleware(['auth:admin'])->name('admin.purchase-orders.store');
Route::put('purchase-orders/update', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'update'])->middleware(['auth:admin'])->name('admin.purchase-orders.update');
Route::get('purchase-orders/create', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'create'])->middleware(['auth:admin'])->name('admin.purchase-orders.create');
Route::get('purchase-orders/print', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'print'])->middleware(['auth:admin'])->name('admin.purchase-orders.print');
Route::get('purchase-orders/approve', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'approve'])->middleware(['auth:admin'])->name('admin.purchase-orders.approve');
Route::get('purchase-orders/edit', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'edit'])->middleware(['auth:admin'])->name('admin.purchase-orders.edit');
Route::get('suppliers/purchase-orders', [App\Http\Controllers\Admin\SupplierController::class, 'purchaseOrders'])->middleware(['auth:admin'])->name('admin.suppliers.purchase-orders');
Route::get('users/assign-role/store', [App\Http\Controllers\UserController::class, 'assignRoleStore'])->name('users.assign-role.store');
Route::get('kitchen/orders/index', [App\Http\Controllers\KitchenController::class, 'orders'])->name('kitchen.orders.index');
Route::get('reservations/index', [App\Http\Controllers\ReservationController::class, 'index'])->name('reservations.index');
Route::get('orders/show', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
Route::put('reservations/update', [App\Http\Controllers\ReservationController::class, 'update'])->name('reservations.update');
Route::get('orders/reservations/summary', [App\Http\Controllers\Admin\OrderController::class, 'reservations'])->middleware(['auth:admin'])->name('admin.orders.reservations.summary');
Route::get('orders/takeaway/summary', [App\Http\Controllers\Admin\OrderController::class, 'takeaway'])->middleware(['auth:admin'])->name('admin.orders.takeaway.summary');
Route::get('orders/summary', [App\Http\Controllers\Admin\OrderController::class, 'summary'])->middleware(['auth:admin'])->name('admin.orders.summary');
Route::get('bills/show', [App\Http\Controllers\Admin\BillController::class, 'show'])->middleware(['auth:admin'])->name('admin.bills.show');
Route::get('inventory/items/added-items', [App\Http\Controllers\Admin\InventoryController::class, 'items'])->middleware(['auth:admin'])->name('admin.inventory.items.added-items');
Route::get('payments/create', [App\Http\Controllers\PaymentController::class, 'create'])->name('payments.create');
Route::get('branch', [App\Http\Controllers\BranchController::class, 'index'])->name('branch');
Route::get('organization', [App\Http\Controllers\OrganizationController::class, 'index'])->name('organization');
Route::get('role', [App\Http\Controllers\RoleController::class, 'index'])->name('role');
Route::get('subscription/expired', [App\Http\Controllers\SubscriptionController::class, 'expired'])->name('subscription.expired');
Route::get('subscription/upgrade', [App\Http\Controllers\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
Route::get('subscription/required', [App\Http\Controllers\SubscriptionController::class, 'required'])->name('subscription.required');