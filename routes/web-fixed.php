<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Middleware\SuperAdmin;

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
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

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

            // JSON endpoints
            Route::get('/{supplier}/pending-grns', [SupplierController::class, 'pendingGrns'])->name('pending-grns');
            Route::get('/{supplier}/pending-pos', [SupplierController::class, 'pendingPos'])->name('pending-pos');
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

            // AJAX endpoints for OrderManagementController
            Route::get('/ajax/items-with-stock', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getItemsWithStock'])->name('ajax.items-with-stock');
            Route::get('/ajax/stewards', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getStewards'])->name('ajax.stewards');
            Route::get('/ajax/available-stewards', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getAvailableStewards'])->name('ajax.available-stewards');
            Route::get('/ajax/stock-alerts', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getStockAlerts'])->name('ajax.stock-alerts');

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

        // Payments
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

        // Purchase Orders
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{po}', [PurchaseOrderController::class, 'show'])->name('show');
            Route::get('/{po}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
            Route::post('/{po}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
            Route::get('/{id}/print', [PurchaseOrderController::class, 'print'])->name('print');
        });

        // Production Management
        Route::prefix('production')->name('production.')->group(function () {
            Route::post('/calculate-ingredients', [ProductionOrderController::class, 'calculateIngredients'])->name('calculate-ingredients');
            Route::post('/calculate-ingredients-from-recipes', [ProductionOrderController::class, 'calculateIngredientsFromRecipes'])->name('calculate-ingredients-from-recipes');
            Route::get('/recipe-details/{itemId}', [ProductionOrderController::class, 'getRecipeDetails'])->name('recipe-details');

            Route::get( '/', [ProductionController::class, 'dashboard'])->name('index');

            // Production requests
            Route::prefix('requests')->name('requests.')->group(function () {
                Route::get('/', [ProductionRequestsMasterController::class, 'index'])->name('index');
                Route::get('/create', [ProductionRequestsMasterController::class, 'create'])->name('create');
                Route::post('/', [ProductionRequestsMasterController::class, 'store'])->name('store');
                Route::get('/manage', [ProductionRequestsMasterController::class, 'manage'])->name('manage');
                Route::get('/aggregate', [ProductionRequestsMasterController::class, 'aggregate'])->name('aggregate');
                Route::get('calculate-ingredients', [ProductionRequestsMasterController::class, 'calculateIngredients'])->name('calculate-ingredients');

                // Specific parameterized routes
                Route::get('/{productionRequest}', [ProductionRequestsMasterController::class, 'show'])->where('productionRequest', '[0-9]+')->name('show');
                Route::post('/{productionRequest}/submit', [ProductionRequestsMasterController::class, 'submit'])->where('productionRequest', '[0-9]+')->name('submit');
                Route::get('/{productionRequest}/approve', [ProductionRequestsMasterController::class, 'showApprovalForm'])->where('productionRequest', '[0-9]+')->name('show-approval');
                Route::post('/{productionRequest}/approve', [ProductionRequestsMasterController::class, 'processApproval'])->where('productionRequest', '[0-9]+')->name('approve');
                Route::post('/{productionRequest}/cancel', [ProductionRequestsMasterController::class, 'cancel'])->where('productionRequest', '[0-9]+')->name('cancel');
            });

            // Production Orders (HQ)
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [ProductionOrderController::class, 'index'])->name('index');
                Route::get('/aggregate', [ProductionRequestsMasterController::class, 'aggregate'])->name('aggregate');
                Route::post('/', [ProductionOrderController::class, 'store_aggregated'])->name('store_aggregated');
                Route::get('/{productionOrder}', [ProductionOrderController::class, 'show'])->name('show');
                Route::post('/{productionOrder}/approve', [ProductionOrderController::class, 'approve'])->name('approve');
                Route::post('/{productionOrder}/cancel', [ProductionOrderController::class, 'cancel'])->name('cancel');
                Route::post('/{productionOrder}/issue-ingredients', [ProductionOrderController::class, 'issueIngredients'])->name('issue-ingredients');
            });

            // Recipe Management
            Route::prefix('recipes')->name('recipes.')->group(function () {
                Route::get('/', [ProductionRecipeController::class, 'index'])->name('index');
                Route::get('/create', [ProductionRecipeController::class, 'create'])->name('create');
                Route::post('/', [ProductionRecipeController::class, 'store'])->name('store');
                Route::get('/{recipe}', [ProductionRecipeController::class, 'show'])->name('show');
                Route::get('/{recipe}/edit', [ProductionRecipeController::class, 'edit'])->name('edit');
                Route::put('/{recipe}', [ProductionRecipeController::class, 'update'])->name('update');
                Route::delete('/{recipe}', [ProductionRecipeController::class, 'destroy'])->name('destroy');
                Route::post('/{recipe}/toggle-status', [ProductionRecipeController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{recipe}/production', [ProductionRecipeController::class, 'getRecipeForProduction'])->name('production');
            });

            // Production Session routes
            Route::prefix('sessions')->name('sessions.')->group(function () {
                Route::get('/', [ProductionSessionController::class, 'index'])->name('index');
                Route::get('/create', [ProductionSessionController::class, 'create'])->name('create');
                Route::post('/', [ProductionSessionController::class, 'store'])->name('store');
                Route::get('/{session}', [ProductionSessionController::class, 'show'])->name('show');
                Route::post('/{session}/start', [ProductionSessionController::class, 'start'])->name('start');
                Route::post('/{session}/cancel', [ProductionSessionController::class, 'cancel'])->name('cancel');
                Route::post('/{session}/issue-ingredients', [ProductionSessionController::class, 'issueIngredients'])->name('issue-ingredients');
                Route::post('/{session}/record-production', [ProductionSessionController::class, 'recordProduction'])->name('record-production');
            });
        });

        // Menu Management
        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::get('/create', [MenuController::class, 'create'])->name('create');
            Route::post('/', [MenuController::class, 'store'])->name('store');
            Route::get('/bulk-create', [MenuController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-store', [MenuController::class, 'bulkStore'])->name('bulk-store');
            Route::get('/calendar', [MenuController::class, 'calendar'])->name('calendar');
            Route::get('/calendar/data', [MenuController::class, 'getCalendarData'])->name('calendar.data');
            Route::get('/{menu}', [MenuController::class, 'show'])->name('show');
            Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
            Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
            Route::get('/{menu}/preview', [MenuController::class, 'preview'])->name('preview');
            Route::post('/{menu}/activate', [MenuController::class, 'activate'])->name('activate');
            Route::post('/{menu}/deactivate', [MenuController::class, 'deactivate'])->name('deactivate');
            Route::post('/bulk-activate', [MenuController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [MenuController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
        });

        // Additional Admin Routes
        Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/debug/routes', [\App\Http\Controllers\Admin\DebugController::class, 'routes'])->name('debug.routes');

        // Employee Management
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
        Route::post('employees/{employee}/restore', [\App\Http\Controllers\Admin\EmployeeController::class, 'restore'])->name('employees.restore');
        Route::patch('employees/{employee}/availability', [\App\Http\Controllers\Admin\EmployeeController::class, 'updateAvailability'])->name('employees.update-availability');
    });
});

/*-------------------------------------------------------------------------
| Super Admin Routes
|------------------------------------------------------------------------*/
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

    // Global Branches Index
    Route::get('branches', [BranchController::class, 'globalIndex'])->name('branches.global');

    // Users Management
    Route::resource('users', UserController::class);
    Route::get('users/{user}/assign-role', [UserController::class, 'assignRoleForm'])->name('users.assign-role');
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role.store');

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Modules
    Route::resource('modules', \App\Http\Controllers\ModuleController::class)->except(['show']);

    // Subscription Plans
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('subscriptions', \App\Http\Controllers\SubscriptionController::class)->only(['edit', 'update']);
});

/*-------------------------------------------------------------------------
| Organization/Branch Activation Routes
|------------------------------------------------------------------------*/
Route::get('admin/organizations/activate', [OrganizationController::class, 'showActivationForm'])->name('admin.organizations.activate.form');
Route::post('admin/organizations/activate', [OrganizationController::class, 'activateOrganization'])->name('admin.organizations.activate.submit');

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('branches/activate', [BranchController::class, 'showActivationForm'])->name('branches.activate.form');
    Route::post('branches/activate', [BranchController::class, 'activateBranch'])->name('branches.activate.submit');
    Route::get('branches/{branch}/summary', [BranchController::class, 'summary'])->name('branches.summary');
    Route::put('branches/{branch}/regenerate-key', [BranchController::class, 'regenerateKey'])->name('branches.regenerate-key');
});

/*-------------------------------------------------------------------------
| API Routes for Admin Dashboard
|------------------------------------------------------------------------*/
Route::prefix('admin/api')->name('admin.api.')->middleware(['auth:admin'])->group(function () {
    // Stock and availability APIs
    Route::get('/stock-summary', [AdminOrderController::class, 'getStockSummary'])->name('stock-summary');
    Route::post('/validate-cart', [AdminOrderController::class, 'validateCart'])->name('validate-cart');
    Route::get('/menu-alternatives/{item}', [AdminOrderController::class, 'getMenuAlternatives'])->name('menu-alternatives');
    Route::get('/real-time-availability/{branch}', [AdminOrderController::class, 'getRealTimeAvailability'])->name('real-time-availability');

    // Product catalog APIs
    Route::get('/menu-items/{branch}', [AdminOrderController::class, 'getMenuItems'])->name('menu-items');
    Route::get('/inventory-items/{branch}', [AdminOrderController::class, 'getInventoryItems'])->name('inventory-items');
    Route::post('/update-menu-availability/{branch}', [AdminOrderController::class, 'updateMenuAvailability'])->name('update-menu-availability');
});

/*-------------------------------------------------------------------------
| Realtime Dashboard Routes
|------------------------------------------------------------------------*/
Route::prefix('admin/dashboard')->name('admin.dashboard.')->middleware(['auth:admin'])->group(function () {
    Route::get('/realtime-inventory', [RealtimeDashboardController::class, 'index'])->name('realtime-inventory');

    // API endpoints for dashboard
    Route::get('/api/recent-orders', [RealtimeDashboardController::class, 'getRecentOrdersApi'])->name('api.recent-orders');
    Route::get('/api/low-stock-items', [RealtimeDashboardController::class, 'getLowStockItemsApi'])->name('api.low-stock-items');
    Route::get('/api/stock-levels-chart', [RealtimeDashboardController::class, 'getStockLevelsChart'])->name('api.stock-levels-chart');
    Route::get('/api/menu-availability-stats', [RealtimeDashboardController::class, 'getMenuAvailabilityStatsApi'])->name('api.menu-availability-stats');
    Route::post('/api/export-stock-report', [RealtimeDashboardController::class, 'exportStockReport'])->name('api.export-stock-report');
    Route::get('/api/dashboard-alerts', [RealtimeDashboardController::class, 'getDashboardAlerts'])->name('api.dashboard-alerts');
    Route::get('/api/dashboard-summary', [RealtimeDashboardController::class, 'getDashboardSummary'])->name('api.dashboard-summary');
});

/*-------------------------------------------------------------------------
| Database Testing Routes (Development Only)
|------------------------------------------------------------------------*/
if (config('app.debug')) {
    Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
        Route::post('/diagnose-table', [DatabaseTestController::class, 'diagnoseTable'])->name('diagnose-table');
        Route::post('/run-migrations', [DatabaseTestController::class, 'runMigrations'])->name('run-migrations');
        Route::post('/run-seeder', [DatabaseTestController::class, 'runSeeder'])->name('run-seeder');
        Route::post('/full-diagnose', [DatabaseTestController::class, 'fullDiagnose'])->name('full-diagnose');
        Route::post('/fresh-migrate', [DatabaseTestController::class, 'freshMigrate'])->name('fresh-migrate');
        Route::post('/test-orders', [DatabaseTestController::class, 'testOrderCreation'])->name('test-orders');
        Route::get('/system-stats', [DatabaseTestController::class, 'getSystemStats'])->name('system-stats');
        Route::get('/order-stats', [DatabaseTestController::class, 'getOrderStats'])->name('order-stats');
        Route::get('/recent-orders', [DatabaseTestController::class, 'getRecentOrders'])->name('recent-orders');
        Route::get('/orders-preview', [DatabaseTestController::class, 'getOrdersPreview'])->name('orders-preview');
    });
}
