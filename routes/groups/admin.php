<?php

use Illuminate\Support\Facades\Route;

// Controller imports for modern syntax
use App\Http\Controllers\AdminAuthTestController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTestPageController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemTransactionController;
use App\Http\Controllers\GoodsTransferNoteController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\GrnDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RealtimeDashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\DatabaseTestController;
use App\Http\Controllers\Admin\CustomerController;
// use App\Http\Controllers\Admin\DigitalMenuController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DebugController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\GrnController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CheckTableAvailabilityController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\BillController;

// admin routes - Updated to use modern    // Authentication routes
    Route::get('auth/debug', [AdminAuthTestController::class, 'checkAuth'])->name('auth.check');
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');
    
    // Main admin routes
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('profile', [AdminController::class, 'profile'])->name('profile.index');
    Route::get('testpage', [AdminTestPageController::class, 'index'])->name('testpage');
    
    // Reservations
    Route::get('reservations', [AdminReservationController::class, 'index'])->name('reservations.index');


Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Authentication routes
    Route::get('auth/debug', [AdminAuthTestController::class, 'checkAuth'])->name('auth.check');
    Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('admin/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('admin/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');
    
    // Main admin routes
    Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('admin/profile', [AdminController::class, 'profile'])->name('profile.index');
    Route::get('admin/testpage', [AdminTestPageController::class, 'index'])->name('testpage');
    
    // Reservations
    Route::get('admin/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('admin/reservations/create', [AdminReservationController::class, 'create'])->name('reservations.create');
    Route::post('admin/reservations', [AdminReservationController::class, 'store'])->name('reservations.store');
    Route::get('admin/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
    Route::get('admin/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('admin/reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
    Route::delete('admin/reservations/{reservation}', [AdminReservationController::class, 'destroy'])->name('reservations.destroy');
    
    // Inventory management
    Route::get('admin/inventory', [ItemDashboardController::class, 'index'])->name('inventory.index');
    Route::get('admin/inventory/dashboard', [ItemDashboardController::class, 'index'])->name('inventory.dashboard');
    Route::get('admin/inventory/items', [ItemMasterController::class, 'index'])->name('inventory.items.index');
    Route::get('admin/inventory/items/create', [ItemMasterController::class, 'create'])->name('inventory.items.create');
    Route::post('admin/inventory/items', [ItemMasterController::class, 'store'])->name('inventory.items.store');
    Route::get('admin/inventory/items/{item}', [ItemMasterController::class, 'show'])->name('inventory.items.show');
    Route::get('admin/inventory/items/{item}/edit', [ItemMasterController::class, 'edit'])->name('inventory.items.edit');
    Route::put('admin/inventory/items/{item}', [ItemMasterController::class, 'update'])->name('inventory.items.update');
    Route::delete('admin/inventory/items/{item}', [ItemMasterController::class, 'destroy'])->name('inventory.items.destroy');
    Route::get('admin/inventory/stock', [ItemTransactionController::class, 'index'])->name('inventory.stock.index');
    Route::post('admin/inventory/stock', [ItemTransactionController::class, 'store'])->name('inventory.stock.store');
    Route::get('admin/inventory/stock/transactions', [ItemTransactionController::class, 'transactions'])->name('inventory.stock.transactions.index');
    
    // GTN (Goods Transfer Note)
    Route::get('admin/inventory/gtn/search-items', [GoodsTransferNoteController::class, 'searchItems'])->name('inventory.gtn.search-items');
    Route::get('admin/inventory/gtn/item-stock', [GoodsTransferNoteController::class, 'getItemStock'])->name('inventory.gtn.item-stock');
    Route::get('admin/inventory/gtn', [GoodsTransferNoteController::class, 'index'])->name('inventory.gtn.index');
    Route::get('admin/inventory/gtn/create', [GoodsTransferNoteController::class, 'create'])->name('inventory.gtn.create');
    Route::post('admin/inventory/gtn', [GoodsTransferNoteController::class, 'store'])->name('inventory.gtn.store');
    Route::get('admin/inventory/gtn/{gtn}', [GoodsTransferNoteController::class, 'show'])->name('inventory.gtn.show');
    
    // Suppliers
    Route::get('admin/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('admin/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('admin/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('admin/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('admin/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('admin/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('admin/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::get('admin/suppliers/{supplier}/pending-grns', [SupplierController::class, 'pendingGrns'])->name('suppliers.pending-grns');
    Route::get('admin/suppliers/{supplier}/pending-pos', [SupplierController::class, 'pendingPos'])->name('suppliers.pending-pos');
    
    // GRN (Goods Received Note)
    Route::get('admin/grn', [GrnDashboardController::class, 'index'])->name('grn.index');
    Route::get('admin/grn/create', [GrnDashboardController::class, 'create'])->name('grn.create');
    Route::post('admin/grn', [GrnDashboardController::class, 'store'])->name('grn.store');
    Route::get('admin/grn/{grn}', [GrnDashboardController::class, 'show'])->name('grn.show');
    
    // Orders management
    Route::get('admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('admin/orders/create', [AdminOrderController::class, 'create'])->name('admin.orders.create');
    Route::post('admin/orders', [AdminOrderController::class, 'store'])->name('admin.orders.store');
    Route::get('admin/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::get('admin/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('admin.orders.edit');
    Route::put('admin/orders/{order}', [AdminOrderController::class, 'update'])->name('admin.orders.update');
    Route::delete('admin/orders/{order}', [AdminOrderController::class, 'destroy'])->name('admin.orders.destroy');
    
    // Takeaway orders
    Route::get('admin/orders/takeaway', [AdminOrderController::class, 'indexTakeaway'])->name('admin.orders.takeaway.index');
    Route::get('admin/orders/takeaway/create', [AdminOrderController::class, 'createTakeaway'])->name('admin.orders.takeaway.create');
    Route::post('admin/orders/takeaway', [AdminOrderController::class, 'storeTakeaway'])->name('admin.orders.takeaway.store');
    Route::get('admin/orders/takeaway/{order}', [AdminOrderController::class, 'showTakeaway'])->name('admin.orders.takeaway.show');
    Route::get('admin/orders/takeaway/{order}/edit', [AdminOrderController::class, 'editTakeaway'])->name('admin.orders.takeaway.edit');
    Route::put('admin/orders/takeaway/{order}', [AdminOrderController::class, 'updateTakeaway'])->name('admin.orders.takeaway.update');
    Route::delete('admin/orders/takeaway/{order}', [AdminOrderController::class, 'destroyTakeaway'])->name('admin.orders.takeaway.destroy');
    
    // Admin order dashboard and type selector
    Route::get('admin/orders/dashboard', [AdminOrderController::class, 'dashboard'])->name('admin.orders.dashboard');
    Route::get('admin/orders/takeaway/type-selector', [AdminOrderController::class, 'takeawayTypeSelector'])->name('admin.orders.takeaway.type-selector');
    
    // Organizations
    Route::get('admin/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('admin/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('admin/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('admin/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('admin/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('admin/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::get('admin/organizations/{organization}/summary', [OrganizationController::class, 'summary'])->name('organizations.summary');
    Route::put('admin/organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->name('organizations.regenerate-key');
    
    // Branches
    Route::get('admin/organizations/{organization}/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('admin/organizations/{organization}/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('admin/organizations/{organization}/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('admin/organizations/{organization}/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('admin/organizations/{organization}/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('admin/organizations/{organization}/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    Route::get('admin/organizations/{organization}/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('admin/organizations/{organization}/branches/{branch}/users/create', [UserController::class, 'create'])->name('branch.users.create');
    Route::get('admin/branches', [BranchController::class, 'globalIndex'])->name('branches.global');
    
    // Users and roles
    Route::get('admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('admin/roles', [RoleController::class, 'index'])->name('roles.index');
    
    // Subscription plans
    Route::get('admin/subscription-plans', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
    Route::get('admin/subscription-plans/create', [SubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
    Route::post('admin/subscription-plans', [SubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
    Route::get('admin/subscription-plans/{subscription_plan}', [SubscriptionPlanController::class, 'show'])->name('subscription-plans.show');
    Route::get('admin/subscription-plans/{subscription_plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
    Route::put('admin/subscription-plans/{subscription_plan}', [SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
    Route::delete('admin/subscription-plans/{subscription_plan}', [SubscriptionPlanController::class, 'destroy'])->name('subscription-plans.destroy');
    
    // Organization and branch activation
    Route::get('admin/organizations/activate', [OrganizationController::class, 'showActivationForm'])->name('organizations.activate.form');
    Route::post('admin/organizations/activate', [OrganizationController::class, 'activateOrganization'])->name('organizations.activate.submit');
    Route::get('admin/branches/activate', [BranchController::class, 'showActivationForm'])->name('branches.activate.form');
    Route::post('admin/branches/activate', [BranchController::class, 'activateBranch'])->name('branches.activate.submit');
    
    // Subscriptions
    Route::get('admin/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('admin/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    
    // Enhanced roles and modules
    Route::get('admin/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('admin/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('admin/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('admin/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('admin/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('admin/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('admin/modules/create', [ModuleController::class, 'create'])->name('modules.create');
    Route::post('admin/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('admin/modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
    Route::put('admin/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('admin/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
    Route::get('admin/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('admin/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    
    // Enhanced user management
    Route::get('admin/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('admin/users', [UserController::class, 'store'])->name('users.store');
    Route::get('admin/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('admin/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('admin/users/{user}/assign-role', [UserController::class, 'assignRoleForm'])->name('users.assign-role');
    Route::post('admin/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role.store');
    Route::get('admin/users/{user}', [UserController::class, 'show'])->name('users.show');
    
    // Enhanced orders
    Route::get('admin/orders/enhanced-create', [AdminOrderController::class, 'enhancedCreate'])->name('orders.enhanced-create');
    Route::post('admin/orders/enhanced-store', [AdminOrderController::class, 'enhancedStore'])->name('orders.enhanced-store');
    Route::post('admin/orders/{order}/confirm-stock', [AdminOrderController::class, 'confirmOrderStock'])->name('orders.confirm-stock');
    Route::delete('admin/orders/{order}/cancel-with-stock', [AdminOrderController::class, 'cancelOrderWithStock'])->name('orders.cancel-with-stock');
    
    // Realtime dashboard
    Route::get('admin/dashboard/realtime-inventory', [RealtimeDashboardController::class, 'index'])->name('dashboard.realtime-inventory');
    Route::get('menus/safety-dashboard', [MenuController::class, 'safetyDashboard'])->name('menus.safety-dashboard');
    
    // Database testing (admin only)
    Route::post('admin/diagnose-table', [DatabaseTestController::class, 'diagnoseTable'])->name('admin.diagnose-table');
    Route::post('admin/run-migrations', [DatabaseTestController::class, 'runMigrations'])->name('admin.run-migrations');
    Route::post('admin/run-seeder', [DatabaseTestController::class, 'runSeeder'])->name('admin.run-seeder');
    Route::post('admin/full-diagnose', [DatabaseTestController::class, 'fullDiagnose'])->name('admin.full-diagnose');
    Route::post('admin/fresh-migrate', [DatabaseTestController::class, 'freshMigrate'])->name('admin.fresh-migrate');
    Route::post('admin/test-orders', [DatabaseTestController::class, 'testOrderCreation'])->name('admin.test-orders');
    Route::get('admin/system-stats', [DatabaseTestController::class, 'getSystemStats'])->name('admin.system-stats');
    Route::get('admin/order-stats', [DatabaseTestController::class, 'getOrderStats'])->name('admin.order-stats');
    Route::get('admin/recent-orders', [DatabaseTestController::class, 'getRecentOrders'])->name('admin.recent-orders');
    Route::get('admin/orders-preview', [DatabaseTestController::class, 'getOrdersPreview'])->name('admin.orders-preview');
    
    // Enhanced admin modules
    Route::get('customers/index', [CustomerController::class, 'index'])->name('customers.index');
    // Route::get('digital-menu/index', [DigitalMenuController::class, 'index'])->name('digital-menu.index');
    Route::get('settings/index', [SettingController::class, 'index'])->name('settings.index');
    Route::get('reports/index', [ReportController::class, 'index'])->name('reports.index');
    Route::get('debug/routes', [DebugController::class, 'routes'])->name('debug.routes');
    Route::get('debug/routes/test', [DebugController::class, 'routes'])->name('debug.routes.test');
    Route::get('debug/routes/generate', [DebugController::class, 'routes'])->name('debug.routes.generate');
    Route::get('debug/routes/export', [DebugController::class, 'routes'])->name('debug.routes.export');
    
    // Employee management
    Route::get('employees/index', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('employees/store', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/show', [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('employees/update', [EmployeeController::class, 'update'])->name('employees.update');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::get('employees/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::get('employees/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::delete('employees/destroy', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    
    // Additional admin modules with proper route names
    Route::get('grn/link-payment', [GrnController::class, 'linkPayment'])->name('grn.link-payment');
    Route::get('payments/show', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('inventory/gtn/items-with-stock', [InventoryController::class, 'gtnItemsWithStock'])->name('inventory.gtn.items-with-stock');
    Route::get('inventory/gtn/update', [InventoryController::class, 'gtnUpdate'])->name('inventory.gtn.update');
    Route::get('inventory/gtn/print', [InventoryController::class, 'gtnPrint'])->name('inventory.gtn.print');
    Route::get('inventory/gtn/edit', [InventoryController::class, 'gtnEdit'])->name('inventory.gtn.edit');
    Route::get('inventory/items/restore', [InventoryController::class, 'itemsRestore'])->name('inventory.items.restore');
    Route::get('inventory/stock/update', [InventoryController::class, 'stockUpdate'])->name('inventory.stock.update');
    Route::get('inventory/stock/edit', [InventoryController::class, 'stockEdit'])->name('inventory.stock.edit');
    Route::get('inventory/stock/show', [InventoryController::class, 'stockShow'])->name('inventory.stock.show');
    
    // Menu management
    Route::get('menus/index', [MenuController::class, 'index'])->name('menus.index');
    Route::get('menus/bulk/store', [MenuController::class, 'bulkStore'])->name('menus.bulk-store');
    Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
    Route::get('menus/calendar/data', [MenuController::class, 'calendarData'])->name('menus.calendar.data');
    Route::post('menus/store', [MenuController::class, 'store'])->name('menus.store');
    Route::get('menus/show', [MenuController::class, 'show'])->name('menus.show');
    Route::put('menus/update', [MenuController::class, 'update'])->name('menus.update');
    Route::get('menus/calendar', [MenuController::class, 'calendar'])->name('menus.calendar');
    Route::get('menus/list', [MenuController::class, 'list'])->name('menus.list');
    Route::get('menus/bulk/create', [MenuController::class, 'bulkCreate'])->name('menus.bulk.create');
    Route::get('menus/preview', [MenuController::class, 'preview'])->name('menus.preview');
    
    // Enhanced order management
    Route::get('orders/archive-old-menus', [OrderController::class, 'archiveOldMenus'])->name('orders.archive-old-menus');
    Route::get('orders/menu-safety-status', [OrderController::class, 'menuSafetyStatus'])->name('orders.menu-safety-status');
    Route::get('orders/reservations/store', [OrderController::class, 'reservationsStore'])->name('orders.reservations.store');
    Route::get('orders/update-cart', [OrderController::class, 'updateCart'])->name('orders.update-cart');
    Route::get('orders/reservations/index', [OrderController::class, 'reservationsIndex'])->name('orders.reservations.index');
    Route::get('orders/orders/reservations/edit', [OrderController::class, 'ordersReservationsEdit'])->name('orders.orders.reservations.edit');
    Route::get('orders/takeaway/branch', [OrderController::class, 'takeawayBranch'])->name('orders.takeaway.branch');
    Route::get('orders/dashboard', [OrderController::class, 'dashboard'])->name('orders.dashboard');
    Route::get('check-table-availability', [CheckTableAvailabilityController::class, 'index'])->name('check-table-availability');
    Route::get('orders/reservations/create', [OrderController::class, 'reservationsCreate'])->name('orders.reservations.create');
    Route::get('orders/reservations/edit', [OrderController::class, 'reservationsEdit'])->name('orders.reservations.edit');
    Route::get('reservations/assign-steward', [ReservationController::class, 'assignSteward'])->name('reservations.assign-steward');
    Route::get('reservations/check-in', [ReservationController::class, 'checkIn'])->name('reservations.check-in');
    Route::get('reservations/check-out', [ReservationController::class, 'checkOut'])->name('reservations.check-out');
    Route::get('orders/orders/reservations/create', [OrderController::class, 'ordersReservationsCreate'])->name('orders.orders.reservations.create');
    
    // GRN additional routes
    Route::put('grn/update', [GrnController::class, 'update'])->name('grn.update');
    Route::get('grn/print', [GrnController::class, 'print'])->name('grn.print');
    Route::get('grn/edit', [GrnController::class, 'edit'])->name('grn.edit');
    Route::get('grn/verify', [GrnController::class, 'verify'])->name('grn.verify');
    
    // Purchase orders
    Route::get('purchase-orders/show', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('purchase-orders/index', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::post('purchase-orders/store', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::put('purchase-orders/update', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::get('purchase-orders/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::get('purchase-orders/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::get('purchase-orders/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
    
    // Payment management
    Route::get('payments/index', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/store', [PaymentController::class, 'store'])->name('payments.store');
    Route::put('payments/update', [PaymentController::class, 'update'])->name('payments.update');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::get('payments/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::get('payments/print', [PaymentController::class, 'print'])->name('payments.print');
    Route::delete('payments/destroy', [PaymentController::class, 'destroy'])->name('payments.destroy');
    
    // Additional supplier routes
    Route::get('suppliers/purchase-orders', [AdminSupplierController::class, 'purchaseOrders'])->name('suppliers.purchase-orders');
    
    // Order summaries
    Route::get('orders/reservations/summary', [OrderController::class, 'reservationsSummary'])->name('orders.reservations.summary');
    Route::get('orders/takeaway/summary', [OrderController::class, 'takeawaySummary'])->name('orders.takeaway.summary');
    Route::get('orders/summary', [OrderController::class, 'summary'])->name('orders.summary');
    
    // Bills
    Route::get('bills/show', [BillController::class, 'show'])->name('bills.show');
    Route::get('inventory/items/added-items', [InventoryController::class, 'itemsAddedItems'])->name('inventory.items.added-items');
    
    // Enhanced order routes for reservation and takeaway flows
    Route::get('admin/orders/reservations/summary', [AdminOrderController::class, 'reservationOrderSummary'])->name('orders.reservations.summary');
    Route::get('admin/orders/takeaway/type-selector', [AdminOrderController::class, 'takeawayTypeSelector'])->name('orders.takeaway.type-selector');
});
