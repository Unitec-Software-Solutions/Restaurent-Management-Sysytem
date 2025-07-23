<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CustomerDashboardController,
    ReservationController,
    AdminReservationController,
    AdminController,
    AdminAuthController,
    OrderController,
    AdminOrderController,
    OrganizationController,
    RoleController,
    BranchController,
    UserController,
    RealtimeDashboardController,
    MenuController,
    KitchenController,
    KotController,
    ModuleController,
    ReportsGenController
};
 // Admin namespace controllers
use App\Http\Controllers\Admin\{
    ProductionOrderController,
    ProductionController,
    ProductionRequestsMasterController,
    ProductionRecipeController,
    ProductionSessionController,
    SubscriptionPlanController,
    PaymentController,
    KitchenStationController,
    MenuItemController,

};

// supplier controllers
use App\Http\Controllers\Admin\{
    SupplierController,
    SupplierPaymentController
};

// Purchase Order Controller
use App\Http\Controllers\Admin\{
    PurchaseOrderController
};
// GRN/GTN/SRN controllers
use App\Http\Controllers\Admin\
{
    //GrnController,
    GrnDashboardController,
    GoodsTransferNoteController,
    StockReleaseNoteController,
    // GoodsTransferItemController,
    // GrnItemController,
};
// inventory controllers
use App\Http\Controllers\Admin\
{
    InventoryController,
    ItemDashboardController,
    ItemCategoryController,
    ItemMasterController,
    ItemTransactionController,
    // ItemStockController (what the heck is this?)
};

// Report controllers
use App\Http\Controllers\Admin\{
    ReportController
};


use App\Http\Controllers\PaymentController as MainPaymentController;
use App\Http\Middleware\SuperAdmin;
use App\Http\Controllers\ReservationWorkflowController;

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
        Route::get('/create-from-reservation/{reservation}', [OrderController::class, 'createFromReservation'])->name('create-from-reservation');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}/summary', [OrderController::class, 'summary'])->whereNumber('order')->name('summary');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->whereNumber('order')->name('edit');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->whereNumber('order')->name('destroy');
        Route::put('/{order}', [OrderController::class, 'update'])->whereNumber('order')->name('update');

        // Stock checking
        Route::post('/check-stock', [OrderController::class, 'checkStock'])->name('check-stock');
        Route::get('/{order}/print-kot', [OrderController::class, 'printKOT'])->whereNumber('order')->name('print-kot');
        Route::get('/{order}/print-kot-pdf', [OrderController::class, 'printKOTPDF'])->whereNumber('order')->name('print-kot-pdf');
        Route::post('/{order}/print-bill', [OrderController::class, 'printBill'])->name('print-bill');
        Route::post('/{order}/mark-preparing', [OrderController::class, 'markAsPreparing'])->name('mark-preparing');
        Route::post('/{order}/mark-ready', [OrderController::class, 'markAsReady'])->name('mark-ready');
        Route::post('/{order}/complete', [OrderController::class, 'completeOrder'])->name('complete');

        // Enhanced KOT functionality
        Route::get('/{order}/check-and-print-kot', [OrderController::class, 'apiCheckAndPrintKOT'])->whereNumber('order')->name('check-and-print-kot');
        Route::get('/{order}/print-kot-direct', [OrderController::class, 'printKOTForOrder'])->whereNumber('order')->name('print-kot-direct');

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
    // Authentication routes (no middleware)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // All authenticated admin routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');

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
                Route::get('/added-items', [ItemMasterController::class, 'added'])->name('added-items');
                Route::get('/create-template/{index}', [ItemMasterController::class, 'getItemFormPartial'])->name('create-template');
                Route::get('/{item}', [ItemMasterController::class, 'show'])->name('show');
                Route::get('/{item}/edit', [ItemMasterController::class, 'edit'])->name('edit');
                Route::put('/{item}', [ItemMasterController::class, 'update'])->name('update');
                Route::delete('/{item}', [ItemMasterController::class, 'destroy'])->whereNumber('item')->name('destroy');
            });

            // Menu eligible items (for creating menu items from item master)
            Route::get('/menu-eligible', [ItemMasterController::class, 'getMenuEligibleItems'])->name('menu-eligible');

            // Stock Management
            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');

                // Transactions routes (must come before {transaction} routes to avoid conflicts)
                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });

                // Edit and Update for specific item+branch combination
                Route::get('/edit/{item_id}/{branch_id}', [ItemTransactionController::class, 'edit'])->where(['item_id' => '[0-9]+', 'branch_id' => '[0-9]+'])->name('edit');
                Route::put('/update/{item_id}/{branch_id}', [ItemTransactionController::class, 'update'])->where(['item_id' => '[0-9]+', 'branch_id' => '[0-9]+'])->name('update');

                // Show individual transaction (must come after specific routes to avoid conflicts)
                Route::get('/{transaction}', [ItemTransactionController::class, 'show'])->where('transaction', '[0-9]+')->name('show');
                Route::delete('/{transaction}', [ItemTransactionController::class, 'destroy'])->where('transaction', '[0-9]+')->name('destroy');
            });

            // API endpoints for inventory stock
            Route::prefix('api/stock')->name('api.stock.')->group(function () {
                Route::get('/summary', [ItemTransactionController::class, 'stockSummary'])->name('summary');
            });

            // GTN Management
            Route::prefix('gtn')->name('gtn.')->group(function () {
                Route::get('/search-items', [GoodsTransferNoteController::class, 'searchItems'])->name('search-items');
                Route::get('/item-stock', [GoodsTransferNoteController::class, 'getItemStock'])->name('item-stock');
                Route::get('/', [GoodsTransferNoteController::class, 'index'])->name('index');
                Route::get('/create', [GoodsTransferNoteController::class, 'create'])->name('create');
                Route::post('/', [GoodsTransferNoteController::class, 'store'])->name('store');
                Route::get('/{gtn}', [GoodsTransferNoteController::class, 'show'])->whereNumber('gtn')->name('show');
                Route::get('/{gtn}/edit', [GoodsTransferNoteController::class, 'edit'])->whereNumber('gtn')->name('edit');
                Route::put('/{gtn}', [GoodsTransferNoteController::class, 'update'])->whereNumber('gtn')->name('update');
                Route::delete('/{gtn}', [GoodsTransferNoteController::class, 'destroy'])->whereNumber('gtn')->name('destroy');
                Route::get('/{gtn}/print', [GoodsTransferNoteController::class, 'print'])->whereNumber('gtn')->name('print');

                // Workflow endpoints
                Route::post('/{gtn}/confirm', [GoodsTransferNoteController::class, 'confirm'])->whereNumber('gtn')->name('confirm');
                Route::post('/{gtn}/receive', [GoodsTransferNoteController::class, 'receive'])->whereNumber('gtn')->name('receive');
                Route::post('/{gtn}/verify', [GoodsTransferNoteController::class, 'verify'])->whereNumber('gtn')->name('verify');
                Route::post('/{gtn}/accept', [GoodsTransferNoteController::class, 'processAcceptance'])->whereNumber('gtn')->name('accept');
                Route::post('/{gtn}/reject', [GoodsTransferNoteController::class, 'reject'])->whereNumber('gtn')->name('reject');
                Route::get('/{gtn}/audit-trail', [GoodsTransferNoteController::class, 'auditTrail'])->whereNumber('gtn')->name('audit-trail');
                Route::post('/{gtn}/change-status', [GoodsTransferNoteController::class, 'changeStatus'])->whereNumber('gtn')->name('change-status');

                // AJAX endpoints
                Route::get('/items-with-stock', [GoodsTransferNoteController::class, 'getItemsWithStock'])->name('items-with-stock');
                Route::get('/search-items-ajax', [GoodsTransferNoteController::class, 'searchItems'])->name('search-items-ajax');
                Route::get('/item-stock-ajax', [GoodsTransferNoteController::class, 'getItemStock'])->name('item-stock-ajax');
            });

            Route::prefix('srn')->name('srn.')->group(function () {
                Route::get('/', [StockReleaseNoteController::class, 'index'])->name('index');
                Route::get('/create', [StockReleaseNoteController::class, 'create'])->name('create');
                Route::post('/', [StockReleaseNoteController::class, 'store'])->name('store');
                Route::get('/{release}', [StockReleaseNoteController::class, 'show'])->whereNumber('release')->name('show');
                Route::get('/{release}/edit', [StockReleaseNoteController::class, 'edit'])->whereNumber('release')->name('edit');
                Route::put('/{release}', [StockReleaseNoteController::class, 'update'])->whereNumber('release')->name('update');
                Route::delete('/{release}', [StockReleaseNoteController::class, 'destroy'])->whereNumber('release')->name('destroy');

                // AJAX endpoints fetch items of that branch with stock
                Route::get('/items-with-stock', [StockReleaseNoteController::class, 'itemsWithStock'])->name('items-with-stock');

                // Verification endpoint
                Route::post('/{release}/verify', [StockReleaseNoteController::class, 'verify'])->whereNumber('release')->name('verify');
            });

        });

        // Item Categories Management
        Route::prefix('item-categories')->name('item-categories.')->group(function () {
            Route::get('/', [ItemCategoryController::class, 'index'])->name('index');
            Route::get('/create', [ItemCategoryController::class, 'create'])->name('create');
            Route::post('/', [ItemCategoryController::class, 'store'])->name('store');
            Route::get('/{itemCategory}', [ItemCategoryController::class, 'show'])->name('show');
            Route::get('/{itemCategory}/edit', [ItemCategoryController::class, 'edit'])->name('edit');
            Route::put('/{itemCategory}', [ItemCategoryController::class, 'update'])->name('update');
            Route::delete('/{itemCategory}', [ItemCategoryController::class, 'destroy'])->name('destroy');
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
            Route::get('/{grn}', [GrnDashboardController::class, 'show'])->whereNumber('grn')->name('show');
            Route::get('/{grn}/edit', [GrnDashboardController::class, 'edit'])->whereNumber('grn')->name('edit');
            Route::put('/{grn}', [GrnDashboardController::class, 'update'])->whereNumber('grn')->name('update');
            Route::delete('/{grn}', [GrnDashboardController::class, 'destroy'])->whereNumber('grn')->name('destroy');
            Route::get('/{grn}/print', [GrnDashboardController::class, 'print'])->name('print');
            Route::post('/verify/{grn}', [GrnDashboardController::class, 'verify'])->name('verify');
        });

        // GRN API routes for super admin organization selection
        Route::prefix('api/grn')->name('api.grn.')->group(function () {
            Route::get('/suppliers-by-organization', [GrnDashboardController::class, 'getSuppliersByOrganization'])->name('suppliers-by-organization');
            Route::get('/branches-by-organization', [GrnDashboardController::class, 'getBranchesByOrganization'])->name('branches-by-organization');
            Route::get('/items-by-organization', [GrnDashboardController::class, 'getItemsByOrganization'])->name('items-by-organization');
        });

        // Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/create', [AdminOrderController::class, 'create'])->name('create');
            Route::post('/', [AdminOrderController::class, 'store'])->name('store');
            Route::get('/today', [ReservationWorkflowController::class, 'showTodaysOrders'])->name('today');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->whereNumber('order')->name('show');
            Route::get('/{order}/edit', [AdminOrderController::class, 'edit'])->whereNumber('order')->name('edit');
            Route::put('/{order}', [AdminOrderController::class, 'update'])->whereNumber('order')->name('update');
            Route::delete('/{order}', [AdminOrderController::class, 'destroy'])->whereNumber('order')->name('destroy');

            // KOT and Printing routes
            Route::get('/{order}/print-kot', [AdminOrderController::class, 'printKOT'])->whereNumber('order')->name('print-kot');
            Route::get('/{order}/print-kot-pdf', [AdminOrderController::class, 'printKOTPDF'])->whereNumber('order')->name('print-kot-pdf');
            Route::post('/{order}/print-bill', [AdminOrderController::class, 'printBill'])->whereNumber('order')->name('print-bill');


            // Enhanced KOT functionality
            Route::get('/{order}/check-and-print-kot', [AdminOrderController::class, 'apiCheckAndPrintKOT'])->whereNumber('order')->name('check-and-print-kot');
            Route::post('/{order}/generate-kot', [KotController::class, 'generateKot'])->whereNumber('order')->name('generate-kot');

            // AJAX endpoints for KOT and status management
            Route::get('/{order}/check-kot', [AdminOrderController::class, 'checkKotItems'])->whereNumber('order')->name('check-kot');
            Route::post('/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->whereNumber('order')->name('update-status');

            // AJAX endpoints for OrderManagementController
            Route::get('/ajax/items-with-stock', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getItemsWithStock'])->name('ajax.items-with-stock');
            Route::get('/ajax/stewards', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getStewards'])->name('ajax.stewards');
            Route::get('/ajax/available-stewards', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getAvailableStewards'])->name('ajax.available-stewards');
            Route::get('/ajax/stock-alerts', [\App\Http\Controllers\Admin\OrderManagementController::class, 'getStockAlerts'])->name('ajax.stock-alerts');

            Route::prefix('takeaway')->name('takeaway.')->group(function () {
                Route::get('/', [AdminOrderController::class, 'indexTakeaway'])->name('index');
                Route::get('/create', [AdminOrderController::class, 'createTakeaway'])->name('create');
                Route::post('/', [AdminOrderController::class, 'storeTakeaway'])->name('store');
                Route::get('/{order}', [AdminOrderController::class, 'showTakeaway'])->whereNumber('order')->name('show');
                Route::get('/{order}/summary', [AdminOrderController::class, 'summaryTakeaway'])->whereNumber('order')->name('summary');
                Route::get('/{order}/edit', [AdminOrderController::class, 'editTakeaway'])->whereNumber('order')->name('edit');
                Route::put('/{order}', [AdminOrderController::class, 'updateTakeaway'])->whereNumber('order')->name('update');
                Route::delete('/{order}', [AdminOrderController::class, 'destroyTakeaway'])->whereNumber('order')->name('destroy');
            });
        });

        // Reservation Workflow Management
        Route::prefix('reservation-workflow')->name('reservation-workflow.')->group(function () {
            Route::get('/initialize', [ReservationWorkflowController::class, 'initializeReservation'])->name('initialize');
            Route::post('/initialize', [ReservationWorkflowController::class, 'initializeReservation'])->name('initialize.post');
            Route::get('/create', [ReservationWorkflowController::class, 'createReservation'])->name('create');
            Route::post('/create', [ReservationWorkflowController::class, 'storeReservation'])->name('store');
            Route::get('/reservation/{reservation}/summary', [ReservationWorkflowController::class, 'showReservationSummary'])->name('reservation.summary');
            Route::get('/reservation/{reservation}/order/create', [ReservationWorkflowController::class, 'showOrderCreation'])->name('order.create');
            Route::post('/reservation/{reservation}/order/store', [ReservationWorkflowController::class, 'storeOrder'])->name('order.store');
            Route::get('/fees/calculate', [ReservationWorkflowController::class, 'calculateFees'])->name('fees.calculate');

            // API routes for dynamic loading
            Route::get('/api/organizations/{organization}/branches', [ReservationWorkflowController::class, 'getBranchesForOrganization'])->name('api.branches');
            Route::get('/api/branches/{branch}/menu-items', [ReservationWorkflowController::class, 'getMenuItemsForBranch'])->name('api.menu-items');
        });

        // Global API routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/organizations/{organization}/branches', [ReservationWorkflowController::class, 'getBranchesForOrganization'])->name('organizations.branches');
            Route::get('/menu-items/branch/{branch}', [ReservationWorkflowController::class, 'getMenuItemsForBranch'])->name('menu-items.branch');
            Route::get('/menu-items/branch/{branch}/active', [OrderController::class, 'getMenuItemsFromActiveMenus'])->name('menu-items.active');
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
                Route::get('calculate-ingredients', [ProductionRequestsMasterController::class, 'calculateIngredients'])->name('calculate-ingredients'); // aggregated ingredients calculation - in use

                // Specific parameterized routes (these must come after static routes)
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
                // Production Orders - Enhanced with ingredient management
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

            // Production Session routes with ingredient management
            Route::prefix('sessions')->name('sessions.')->group(function () {
                Route::get('/', [ProductionSessionController::class, 'index'])->name('index');
                Route::get('/create', [ProductionSessionController::class, 'create'])->name('create');
                Route::post('/', [ProductionSessionController::class, 'store'])->name('store');
                Route::get('/{session}', [ProductionSessionController::class, 'show'])->name('show');
                Route::post('/{session}/start', [ProductionSessionController::class, 'start'])->name('start');
                Route::post('/{session}/cancel', [ProductionSessionController::class, 'cancel'])->name('cancel');
                Route::post('/{session}/issue-ingredients', [ProductionSessionController::class, 'issueIngredients'])->name('issue-ingredients');
                Route::post('/{session}/record-production', [ProductionSessionController::class, 'recordProduction'])->name('record-production');
                Route::post('/{productionOrder}/complete-production', [ProductionSessionController::class, 'completeProduction'])->name('complete-production');
            });


        });

        Route::prefix('reports')->name('reports.')->group(function () {
            // Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::prefix('sales')->name('sales.')->group(function () {
                Route::get('/', [ReportController::class, 'salesReport'])->name('index');
            });
            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [ReportController::class, 'inventoryReport'])->name('index');
                Route::get('/stock', [ReportController::class, 'inventoryStock'])->name('stock');
                Route::get('/category', [ReportController::class, 'categoryReport'])->name('category');
                Route::get('/grn', [ReportController::class, 'inventoryGrn'])->name('grn');
                Route::get('/gtn', [ReportController::class, 'inventoryGtn'])->name('gtn');
                Route::get('/srn', [ReportController::class, 'inventorySrn'])->name('srn');
                // Route::get('/items', [ReportController::class, 'inventory_items'])->name('items');
                // Route::get('/summary', [ReportController::class, 'inventory_summary'])->name('summary');
            });

        });

        // Additional Admin Routes
        Route::get('/debug-user', function () {return view('admin.debug-user');})->name('debug-user');
        // Route::get('/reports', function () {return view('admin.reports.index');})->name('reports.view');
        Route::get('/digital-menu', function () {return view('admin.digital-menu.index');})->name('digital-menu.index');
        Route::get('/settings', function () {return view('admin.settings.index');})->name('settings.view');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');

    });

});

// Admin user management routes
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('users/{admin}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::get('users/{admin}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{admin}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{admin}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
});

/*-------------------------------------------------------------------------
| Debugging Route for Branches - Development Only
|------------------------------------------------------------------------*/
// Add this route in the admin middleware group
Route::middleware(['auth:admin'])->group(function () {
    // API routes for super admin organization selection
    Route::get('/admin/api/organizations/{organization}/categories', [
        \App\Http\Controllers\Admin\ItemCategoryController::class,
        'getByOrganization'
    ])->name('admin.api.organizations.categories');

    // Universal admin API route for getting branches by organization
    Route::get('/admin/api/organizations/{organization}/branches', [
        \App\Http\Controllers\BranchController::class,
        'getBranchesByOrganization'
    ])->name('admin.api.organizations.branches');

    // Menu categories API route for getting branches by organization
    Route::get('/admin/api/menu-categories/organizations/{organization}/branches', [
        \App\Http\Controllers\Admin\MenuCategoryController::class,
        'getBranchesForOrganization'
    ])->name('admin.api.menu-categories.organizations.branches');
});

Route::get('/debug/branches', function() {
    $user = auth('admin')->user();
    $branches = \App\Models\Branch::when($user && !$user->is_super_admin, function($query) use ($user) {
        return $query->where('organization_id', $user->organization_id);
    })->get();

    return response()->json([
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'organization_id' => $user->organization_id,
            'is_super_admin' => $user->is_super_admin ?? false
        ] : null,
        'branches' => $branches->map(function($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'organization_id' => $branch->organization_id,
                'is_active' => $branch->is_active
            ];
        })
    ]);
})->middleware(['auth:admin']);

// Super Admin Routes
Route::middleware(['auth:admin', SuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Organization Dashboard
    Route::get('dashboard/organizations', [OrganizationController::class, 'dashboard'])->name('organizations.dashboard');

    // Organization activation index - must come before resource routes to avoid conflicts
    Route::get('organizations/activation', [OrganizationController::class, 'activationIndex'])->name('organizations.activation.index');

    // Organizations CRUD (explicitly exclude show to avoid conflicts)
    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show')->where('organization', '[0-9]+');
    Route::get('organizations/{organization}/summary', [OrganizationController::class, 'summary'])->name('organizations.summary');
    Route::put('organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->name('organizations.regenerate-key');
    Route::get('organizations/{organization}/activate', [OrganizationController::class, 'showActivateForm'])->name('organizations.activate.form');
    Route::post('organizations/{organization}/activate', [OrganizationController::class, 'activate'])->name('organizations.activate');

    // Organization Details & Admin Login
    Route::get('organizations/{organization}/details', [OrganizationController::class, 'getOrganizationDetails'])->name('organizations.details');
    Route::post('organizations/{organization}/login-as-admin', [OrganizationController::class, 'loginAsOrgAdmin'])->name('organizations.login-as-admin');

    // Branches: Organization-specific CRUD
    Route::prefix('organizations/{organization}')->group(function () {
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
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

// Organization Activation Routes - Accessible by both Super Admin and Organization Admin
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('organizations/{organization}/activate-by-key', [OrganizationController::class, 'activateByKey'])->name('organizations.activate.by-key');
});

// Branch summary and regenerate key
Route::middleware(['auth:admin'])->group(function () {
    Route::get('branches/{branch}/summary', [BranchController::class, 'summary'])->name('branches.summary');
    Route::put('branches/{branch}/regenerate-key', [BranchController::class, 'regenerateKey'])->name('branches.regenerate-key');
});

// Show activation form for all admins

Route::prefix('admin')->name('admin.')->middleware('auth:admin')->group(function () {
    Route::get('branches/activate', [BranchController::class, 'showActivationForm'])->name('branches.activate.form');
    Route::post('branches/activate', [BranchController::class, 'activateBranch'])->name('branches.activate.submit');
});

Route::middleware(['web', 'auth:admin', App\Http\Middleware\SuperAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Organizations CRUD

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
        Route::resource('roles', RoleController::class);
        Route::resource('modules', ModuleController::class)->except(['show']);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    });

// User Management Routes - Accessible by Superadmin, Org Admin, and Branch Admin with permissions
Route::middleware(['auth:admin'])
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

    // Organization branches API for admin orders
    Route::get('/organization-branches', [AdminOrderController::class, 'getBranchesForOrganization']);
});

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

// Payment Management
Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
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
// Route::get('digital-menu/index', [App\Http\Controllers\Admin\DigitalMenuController::class, 'index'])->middleware(['auth:admin'])->name('admin.digital-menu.index');
Route::get('settings/index', [App\Http\Controllers\Admin\SettingController::class, 'index'])->middleware(['auth:admin'])->name('admin.settings.index');
Route::get('admin/reports/index', [App\Http\Controllers\Admin\ReportController::class, 'index'])->middleware(['auth:admin'])->name('admin.reports.index');
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
// ::get('grn/link-payment', [GrnDashboardController::class, 'linkPayment'])->middleware(['auth:admin'])->name('admin.grn.link-payment');


Route::post('inventory/items/restore', [ItemMasterController::class, 'restore'])->middleware(['auth:admin'])->name('admin.inventory.items.restore');
// Menu routes - properly ordered to avoid conflicts
Route::get('menus/index', [App\Http\Controllers\Admin\MenuController::class, 'index'])->middleware(['auth:admin'])->name('admin.menus.index');
Route::get('menus/list', [App\Http\Controllers\Admin\MenuController::class, 'list'])->middleware(['auth:admin'])->name('admin.menus.list');
Route::get('menus/manager', [App\Http\Controllers\Admin\MenuController::class, 'manager'])->middleware(['auth:admin'])->name('admin.menus.manager');
Route::get('menus/create', [App\Http\Controllers\Admin\MenuController::class, 'create'])->middleware(['auth:admin'])->name('admin.menus.create');
Route::post('menus/store', [App\Http\Controllers\Admin\MenuController::class, 'store'])->middleware(['auth:admin'])->name('admin.menus.store');
Route::get('menus/manager', [App\Http\Controllers\Admin\MenuController::class, 'manager'])->middleware(['auth:admin'])->name('admin.menus.manager');
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

Route::get('purchase-orders/show', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->middleware(['auth:admin'])->name('admin.purchase-orders.show');
Route::get('purchase-orders/index', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->middleware(['auth:admin'])->name('admin.purchase-orders.index');
Route::post('purchase-orders/store', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->middleware(['auth:admin'])->name('admin.purchase-orders.store');
Route::put('purchase-orders/update', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'update'])->middleware(['auth:admin'])->name('admin.purchase-orders.update');
Route::get('purchase-orders/create', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'create'])->middleware(['auth:admin'])->name('admin.purchase-orders.create');
Route::get('purchase-orders/print', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'print'])->middleware(['auth:admin'])->name('admin.purchase-orders.print');
Route::get('purchase-orders/approve', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'approve'])->middleware(['auth:admin'])->name('admin.purchase-orders.approve');
Route::get('purchase-orders/edit', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'edit'])->middleware(['auth:admin'])->name('admin.purchase-orders.edit');
Route::get('suppliers/index', [App\Http\Controllers\Admin\SupplierController::class, 'index'])->middleware(['auth:admin'])->name('admin.suppliers.index');
Route::get('users/assign-role/store', [App\Http\Controllers\UserController::class, 'assignRoleStore'])->name('users.assign-role.store');
Route::get('kitchen/orders/index', [App\Http\Controllers\KitchenController::class, 'orders'])->name('kitchen.orders.index');
Route::get('reservations/index', [App\Http\Controllers\ReservationController::class, 'index'])->name('reservations.index');
Route::get('orders/show', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
Route::put('reservations/update', [App\Http\Controllers\ReservationController::class, 'update'])->name('reservations.update');
Route::get('orders/reservations/summary', [App\Http\Controllers\Admin\OrderController::class, 'reservations'])->middleware(['auth:admin'])->name('admin.orders.reservations.summary');
Route::get('orders/takeaway/summary', [App\Http\Controllers\Admin\OrderController::class, 'takeaway'])->middleware(['auth:admin'])->name('admin.orders.takeaway.summary');
Route::get('orders/summary', [App\Http\Controllers\Admin\OrderController::class, 'summary'])->middleware(['auth:admin'])->name('admin.orders.summary');
Route::get('bills/show', [App\Http\Controllers\Admin\BillController::class, 'show'])->middleware(['auth:admin'])->name('admin.bills.show');

Route::get('payments/create', [App\Http\Controllers\PaymentController::class, 'create'])->name('payments.create');
Route::get('branch', [App\Http\Controllers\BranchController::class, 'index'])->name('branch');

Route::get('role', [App\Http\Controllers\RoleController::class, 'index'])->name('role');
Route::get('subscription/expired', [App\Http\Controllers\SubscriptionController::class, 'expired'])->name('subscription.expired');
Route::get('subscription/upgrade', [App\Http\Controllers\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
Route::get('subscription/required', [App\Http\Controllers\SubscriptionController::class, 'required'])->name('subscription.required');

/*-------------------------------------------------------------------------
| API Routes
|------------------------------------------------------------------------*/
Route::prefix('api')->middleware(['web'])->group(function () {
    // Organization branches (Public - for guests)
    Route::get('/organizations/{organization}/branches', [ReservationController::class, 'getBranches'])
        ->name('api.organizations.branches');

    // Organization branches (Public - alternative endpoint)
    Route::get('/public/organizations/{organization}/branches', [BranchController::class, 'getBranchesPublic'])
        ->name('api.public.organizations.branches');

    // Branch availability
    Route::get('/branches/{branch}/availability', [ReservationController::class, 'getAvailableTimeSlots'])
        ->name('api.branches.availability');
});

// Remove the duplicate test route - keep only one for debugging
Route::get('/test-branches/{organization}', function($organizationId) {
    try {
        $controller = app(ReservationController::class);
        return $controller->getBranches($organizationId);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
})->name('test.branches');


Route::get('/test-item-master', function () {
    try {
        echo "Testing ItemMaster in web context...<br>";
        echo "Database: " . config('database.default') . "<br>";
        echo "Connection: " . \Illuminate\Support\Facades\DB::connection()->getName() . "<br>";

        // Test table existence
        $exists = \Illuminate\Support\Facades\DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'item_master')");
        echo "Table exists: " . ($exists[0]->exists ? 'YES' : 'NO') . "<br>";

        // Test direct query
        $count = \Illuminate\Support\Facades\DB::table('item_master')->count();
        echo "Direct count: $count<br>";

        // Test model count
        $modelCount = \App\Models\ItemMaster::count();
        echo "Model count: $modelCount<br>";

        // Test with relationship
        $query = \App\Models\ItemMaster::with('itemCategory');
        $relationCount = $query->count();
        echo "With relation count: $relationCount<br>";

        // Test pagination - THIS IS WHERE THE ERROR MIGHT OCCUR
        $pagination = $query->paginate(15);
        echo "Pagination count: " . $pagination->count() . "<br>";
        echo "Pagination total: " . $pagination->total() . "<br>";

        // Test the exact same query as in the controller with organization filter
        echo "<hr>Testing with organization filter (mimicking controller):<br>";
        $orgQuery = \App\Models\ItemMaster::with('itemCategory');
        $orgQuery->where('organization_id', 2); // Use the test org we created

        echo "Org query count: " . $orgQuery->count() . "<br>";
        $orgPagination = $orgQuery->paginate(15);
        echo "Org pagination count: " . $orgPagination->count() . "<br>";

        echo "All tests passed!";

    } catch (Exception $e) {
        echo "<h2>Error occurred:</h2>";
        echo "Message: " . $e->getMessage() . "<br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
        if ($e instanceof \Illuminate\Database\QueryException) {
            echo "SQL: " . $e->getSql() . "<br>";
            echo "Bindings: " . json_encode($e->getBindings()) . "<br>";
        }
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

// Test route for menu items system
Route::get('/test-menu-items', function () {
    return view('test-menu-items');
})->name('test.menu-items');

// Organization Admin Routes - For organization admins to manage their restaurant
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Organization Admin Dashboard
    Route::get('org-dashboard', [OrganizationController::class, 'orgDashboard'])->name('org-dashboard');

    // Switch back to super admin
    Route::get('switch-back-to-super-admin', [OrganizationController::class, 'switchBackToSuperAdmin'])->name('switch-back-to-super-admin');

    // Organization activation index - accessible to both super admins and organization admins
    Route::get('organizations/activation', [OrganizationController::class, 'activationIndex'])->name('organizations.activation.index');
    Route::post('organizations/{organization}/activate-by-key', [OrganizationController::class, 'activateByKey'])->name('organizations.activate.by-key');

    // Branch summary and regenerate key
    Route::get('branches/{branch}/summary', [BranchController::class, 'summary'])->name('branches.summary');
    Route::put('branches/{branch}/regenerate-key', [BranchController::class, 'regenerateKey'])->name('branches.regenerate-key');

    // Show activation form for all admins
    Route::get('branches/activate', [BranchController::class, 'showActivationForm'])->name('branches.activate.form');
    Route::post('branches/activate', [BranchController::class, 'activateBranch'])->name('branches.activate.submit');
});

/*-------------------------------------------------------------------------
| AJAX Routes
|------------------------------------------------------------------------*/
Route::middleware(['auth:admin'])->group(function () {
    // Branches
    Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Admins
    Route::get('admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
    Route::put('admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
    Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');

    // AJAX Modal Details Routes
    Route::get('branches/{branch}/details', [BranchController::class, 'getBranchDetails'])->name('branches.details');
    Route::get('admins/{admin}/details', [AdminController::class, 'getAdminDetails'])->name('admins.details');
});

// Organization Management AJAX Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::post('/', [InventoryController::class, 'store'])->name('store');
    Route::put('/{inventoryItem}/stock', [InventoryController::class, 'updateStock'])->name('update-stock');
});

Route::prefix('menus')->name('menus.')->group(function () {
    Route::get('/', [MenuController::class, 'getMenus'])->name('index');
    Route::post('/', [MenuController::class, 'storeMenu'])->name('store');
    Route::post('/items', [MenuController::class, 'storeMenuItem'])->name('items.store');
});

Route::prefix('orders')->name('orders.')->group(function () {
    // Route::post('/takeaway', [OrderController::class, 'createTakeawayOrder'])->name('takeaway.store');
});

Route::prefix('reservations')->name('reservations.')->group(function () {
    Route::post('/admin-create', [ReservationController::class, 'createReservationFromAdmin'])->name('admin-create');
});

// Kitchen Management Routes
Route::prefix('admin/kitchen')->name('admin.kitchen.')->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('index');
    Route::get('/status', [KitchenController::class, 'getStatus'])->name('status');
    Route::get('/queue', [KitchenController::class, 'queue'])->name('queue.index');

    // KOT Management
    Route::prefix('kots')->name('kots.')->group(function () {
        Route::get('/', [KotController::class, 'index'])->name('index');
        Route::get('/print-all', [KotController::class, 'printAll'])->name('print-all');
        Route::get('/{kot}/print', [KotController::class, 'print'])->name('print');
        Route::patch('/{kot}/status', [KotController::class, 'updateStatus'])->name('update-status');
    });

    // Kitchen Stations
    Route::prefix('stations')->name('stations.')->group(function () {
        Route::get('/', [KitchenStationController::class, 'index'])->name('index');
        Route::get('/create', [KitchenStationController::class, 'create'])->name('create');
        Route::post('/', [KitchenStationController::class, 'store'])->name('store');
        Route::get('/{station}/edit', [KitchenStationController::class, 'edit'])->name('edit');
        Route::put('/{station}', [KitchenStationController::class, 'update'])->name('update');
        Route::patch('/{station}/toggle', [KitchenStationController::class, 'toggleStatus'])->name('toggle');
    });
});

/*-------------------------------------------------------------------------
| Menu Items Management Routes
|------------------------------------------------------------------------*/
Route::prefix('admin/menu-items')->name('admin.menu-items.')->middleware(['auth:admin'])->group(function () {
    // Standard CRUD routes
    Route::get('/', [MenuItemController::class, 'index'])->name('index');
    Route::get('/enhanced', [MenuItemController::class, 'enhancedIndex'])->name('enhanced.index');
    Route::get('/create', [MenuItemController::class, 'create'])->name('create');

    // Enhanced KOT specific routes (MUST be before dynamic routes)
    Route::get('/create-kot', [MenuItemController::class, 'createKotForm'])->name('create-kot');
    Route::post('/create-kot', [MenuItemController::class, 'createKotItems'])->name('store-kot');

    // Standalone KOT creation routes
    Route::get('/create-standalone-kot', [MenuItemController::class, 'createStandaloneKotForm'])->name('standalone-kot.create');
    Route::post('/create-standalone-kot', [MenuItemController::class, 'createStandaloneKotItems'])->name('standalone-kot.store');

    // AJAX routes
    Route::get('/api/items', [MenuItemController::class, 'getItems'])->name('api.items');
    Route::get('/all-items', [MenuItemController::class, 'getAllMenuItems'])->name('all-items');
    Route::get('/by-branch', [AdminOrderController::class, 'getMenuItems'])->name('by-branch');
    Route::get('/menu-eligible-items', [MenuItemController::class, 'getMenuEligibleItems'])->name('menu-eligible-items');
    Route::get('/activated-items', [MenuItemController::class, 'getActivatedMenuItems'])->name('activated-items');

    // Bulk operations with enhanced validation
    Route::post('/create-from-item-master', [MenuItemController::class, 'createFromItemMaster'])->name('create-from-item-master');

    // Dynamic routes (MUST be after specific routes)
    Route::post('/', [MenuItemController::class, 'store'])->name('store');
    Route::get('/{menuItem}', [MenuItemController::class, 'show'])->name('show');
    Route::get('/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('edit');
    Route::patch('/{menuItem}', [MenuItemController::class, 'update'])->name('update');
    Route::delete('/{menuItem}', [MenuItemController::class, 'destroy'])->name('destroy');
});

/*-------------------------------------------------------------------------
| Menu Categories Management Routes
|------------------------------------------------------------------------*/
Route::prefix('admin/menu-categories')->name('admin.menu-categories.')->middleware(['auth:admin'])->group(function () {
    // Standard CRUD routes
    Route::get('/', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'store'])->name('store');
    Route::get('/{menuCategory}', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'show'])->name('show');
    Route::get('/{menuCategory}/edit', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'edit'])->name('edit');
    Route::put('/{menuCategory}', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'update'])->name('update');
    Route::delete('/{menuCategory}', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'destroy'])->name('destroy');

    // AJAX routes
    Route::get('/api/branches/{branch}/categories', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'getCategoriesForBranch'])->name('api.branch-categories');
    Route::post('/api/sort-order', [\App\Http\Controllers\Admin\MenuCategoryController::class, 'updateSortOrder'])->name('api.sort-order');
});

// User Authentication Routes (for regular users)
Route::middleware('guest')->group(function () {
    Route::get('/user/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('user.login');
    Route::post('/user/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('user.login.submit');
});

// User Dashboard Routes (for authenticated regular users)
Route::middleware('auth')->group(function () {
    Route::post('/user/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('user.logout');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/staff', [App\Http\Controllers\DashboardController::class, 'staff'])->name('dashboard.staff');
    Route::get('/dashboard/management', [App\Http\Controllers\DashboardController::class, 'management'])->name('dashboard.management');
});

// Include reservation workflow routes
require __DIR__.'/reservation_workflow.php';

// Include public route groups
require __DIR__.'/groups/public.php';

// API route for getting menu items from active menus
Route::get('/api/menu-items/branch/{branch}/active', [OrderController::class, 'getMenuItemsFromActiveMenus'])->name('api.menu-items.active');

Route::get('admin/kots/{kot}/print', [\App\Http\Controllers\KotController::class, 'print'])
    ->name('admin.kots.print')
    ->middleware(['auth:admin']);

// Export Routes - Multi-sheet Excel exports
Route::middleware(['auth:admin'])->prefix('admin/exports')->name('admin.exports.')->group(function () {
    Route::get('/test', [\App\Http\Controllers\ReportsGenController::class, 'testExport'])->name('test');
    Route::get('.', [\App\Http\Controllers\ReportsGenController::class, 'handleMultiSheetExport'])->name('multisheet');
});

// Roles CRUD - Accessible by Super Admin
Route::middleware(['auth:admin', SuperAdmin::class])->group(function () {
    Route::resource('roles', RoleController::class);

});
