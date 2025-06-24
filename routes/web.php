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
    Route::post('/login', [AdminAuthController::class, 'login']);
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
    });

    // Global Branches Index (for Super Admin to see all branches)
    Route::get('branches', [BranchController::class, 'globalIndex'])->name('branches.global');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');


    // Roles & Permissions
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

    // Subscription Plans
    Route::resource('subscription-plans', \App\Http\Controllers\SubscriptionPlanController::class);
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
        Route::resource('subscription-plans', \App\Http\Controllers\SubscriptionPlanController::class);

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
    Route::resource('subscription-plans', \App\Http\Controllers\SubscriptionPlanController::class);

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
        Route::get('admin/organizations/{organization}/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::get('admin/organizations/{organization}/branches/{branch}/users/create', [UserController::class, 'create'])->name('admin.branch.users.create');
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
});