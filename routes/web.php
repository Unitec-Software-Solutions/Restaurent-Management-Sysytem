<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
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
    UserController,
    ModuleController,
    GoodsTransferNoteController,
    ProductionRequestsMasterController,
    ProductionOrderController,
    ProductionRequestItemController,
    ProductionSessionController,
    ProductionController,
    RecipeController
};
use App\Http\Middleware\SuperAdmin;
use App\Models\Recipe;

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
        Route::resource('reservations', AdminReservationController::class);
        Route::post('reservations/{reservation}/assign-steward', [AdminReservationController::class, 'assignSteward'])
            ->name('reservations.assign-steward');
        Route::post('reservations/{reservation}/check-in', [AdminReservationController::class, 'checkIn'])
            ->name('reservations.check-in');
        Route::post('reservations/{reservation}/check-out', [AdminReservationController::class, 'checkOut'])
            ->name('reservations.check-out');
        Route::get('/check-table-availability', [AdminReservationController::class, 'checkTableAvailability'])
            ->name('check-table-availability');

        // Employee Management
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmployeeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\EmployeeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\EmployeeController::class, 'store'])->name('store');
            Route::get('/{employee}', [\App\Http\Controllers\Admin\EmployeeController::class, 'show'])->name('show');
            Route::get('/{employee}/edit', [\App\Http\Controllers\Admin\EmployeeController::class, 'edit'])->name('edit');
            Route::put('/{employee}', [\App\Http\Controllers\Admin\EmployeeController::class, 'update'])->name('update');
            Route::delete('/{employee}', [\App\Http\Controllers\Admin\EmployeeController::class, 'destroy'])->name('destroy');
            Route::post('/{employee}/restore', [\App\Http\Controllers\Admin\EmployeeController::class, 'restore'])->name('restore');
        });

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
            Route::prefix('reservations/{reservation}')->name('orders.reservations.')->group(function () {
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
               // Route::get('/create', [ItemTransactionController::class, 'create'])->name('create'); removed admin.inventory.stock.create route
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

            // GTN (Goods Transfer Note) Management
            Route::prefix('gtn')->name('gtn.')->group(function () {
                // AJAX endpoints must come before parameterized routes
                Route::get('/items-with-stock', [GoodsTransferNoteController::class, 'getItemsWithStock'])->name('items-with-stock');
                Route::get('/search-items', [GoodsTransferNoteController::class, 'searchItems'])->name('search-items');
                Route::get('/item-stock', [GoodsTransferNoteController::class, 'getItemStock'])->name('item-stock');

                // Standard CRUD routes
                Route::get('/', [GoodsTransferNoteController::class, 'index'])->name('index');
                Route::get('/create', [GoodsTransferNoteController::class, 'create'])->name('create');
                Route::post('/', [GoodsTransferNoteController::class, 'store'])->name('store');
                Route::get('/{gtn}', [GoodsTransferNoteController::class, 'show'])->whereNumber('gtn')->name('show');
                Route::get('/{gtn}/edit', [GoodsTransferNoteController::class, 'edit'])->whereNumber('gtn')->name('edit');
                Route::put('/{gtn}', [GoodsTransferNoteController::class, 'update'])->whereNumber('gtn')->name('update');
                Route::delete('/{gtn}', [GoodsTransferNoteController::class, 'destroy'])->whereNumber('gtn')->name('destroy');
                Route::get('/{gtn}/print', [GoodsTransferNoteController::class, 'print'])->name('print');

                // Enhanced workflow routes for unified GTN system
                Route::post('/{gtn}/confirm', [GoodsTransferNoteController::class, 'confirm'])->whereNumber('gtn')->name('confirm');
                Route::post('/{gtn}/receive', [GoodsTransferNoteController::class, 'receive'])->whereNumber('gtn')->name('receive');
                Route::post('/{gtn}/verify', [GoodsTransferNoteController::class, 'verify'])->whereNumber('gtn')->name('verify');
                Route::post('/{gtn}/accept', [GoodsTransferNoteController::class, 'processAcceptance'])->whereNumber('gtn')->name('accept');
                Route::post('/{gtn}/reject', [GoodsTransferNoteController::class, 'reject'])->whereNumber('gtn')->name('reject');
                Route::get('/{gtn}/audit-trail', [GoodsTransferNoteController::class, 'auditTrail'])->whereNumber('gtn')->name('audit-trail');

                // Legacy status management (for backward compatibility)
                Route::post('/{gtn}/change-status', [GoodsTransferNoteController::class, 'changeStatus'])->whereNumber('gtn')->name('change-status');
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
            Route::get('/{supplier}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('purchase-orders');
            Route::get('/{supplier}/grns', [SupplierController::class, 'goodsReceived'])->name('grns');

            //  Supplier json (remove  later | only for testing)
            Route::get('/{supplier}/pending-grns', [SupplierController::class, 'pendingGrns']);
            Route::get('/{supplier}/pending-pos', [SupplierController::class, 'pendingPos']);

            // Route::get('/{supplier}/pending-grns-pay', [SupplierPaymentController::class, 'getPendingGrns'])->name('pending-grns-pay');
            // Route::get('/{supplier}/pending-pos-pay', [SupplierPaymentController::class, 'getPendingPos'])->name('pending-pos-pay');
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
            // AJAX routes for pending GRNs and POs
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
            Route::post('/calculate-ingredients', [ProductionOrderController::class, 'calculateIngredients'])->name('.calculate-ingredients');
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
                Route::get('/', [RecipeController::class, 'index'])->name('index');
                Route::get('/create', [RecipeController::class, 'create'])->name('create');
                Route::post('/', [RecipeController::class, 'store'])->name('store');
                Route::get('/{recipe}', [RecipeController::class, 'show'])->name('show');
                Route::get('/{recipe}/edit', [RecipeController::class, 'edit'])->name('edit');
                Route::put('/{recipe}', [RecipeController::class, 'update'])->name('update');
                Route::delete('/{recipe}', [RecipeController::class, 'destroy'])->name('destroy');
                Route::post('/{recipe}/toggle-status', [RecipeController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{recipe}/production', [RecipeController::class, 'getRecipeForProduction'])->name('production');
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
            });


        });



        // Additional Admin Routes
        Route::get('/testpage', function () {return view('admin.testpage');})->name('testpage');
        Route::get('/debug-user', function () {return view('admin.debug-user');})->name('debug-user');
        Route::get('/reports', function () {return view('admin.reports.index');})->name('reports.index');
        Route::get('/customers', function () {return view('admin.customers.index');})->name('customers.index');
        Route::get('/digital-menu', function () {return view('admin.digital-menu.index');})->name('digital-menu.index');
        Route::get('/settings', function () {return view('admin.settings.index');})->name('settings.index');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');

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

Route::middleware(['auth:admin', 'module:reservation'])->group(function () {
    Route::resource('reservations', ReservationController::class);
});

Route::middleware(['auth:admin', 'module:inventory'])->group(function () {
    Route::prefix('inventory')->group(function () {
        // Inventory routes
    });
});
// Repeat for other modules
Route::middleware(['auth:admin', 'module:reservation_management'])->group(function () {
    // Reservation management routes
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


