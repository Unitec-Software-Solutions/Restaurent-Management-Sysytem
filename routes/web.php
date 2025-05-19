<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingController;

// Remove or comment out the default welcome route
// Route::get('/', function () {
//     return view('welcome');
// });

// Redirect root URL to /frontend
Route::get('/', function () {
    return redirect('/frontend');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    //  Route::get('/items/create', [InventoryItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    //  Route::post('/items', [InventoryItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
});

Route::prefix('inventory/stock')->name('inventory.stock.')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::get('/create', [StockController::class, 'create'])->name('create');
    Route::post('/store', [StockController::class, 'store'])->name('store');
    Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
    Route::put('/{stock}', [StockController::class, 'update'])->name('update');
    Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Reservation routes
Route::get('/reservation/start', [ReservationsController::class, 'start'])->name('reservation.start');
Route::post('/reservation/check-phone', [ReservationsController::class, 'checkPhone'])->name('reservation.check-phone');
Route::post('/reservations/proceed-as-guest', [ReservationsController::class, 'proceedAsGuest'])->name('reservations.proceed-as-guest');
Route::get('/reservations/choose-action', [ReservationsController::class, 'chooseAction'])->name('reservations.choose-action');
Route::get('/reservation/create', [ReservationsController::class, 'create'])->name('reservation.create');
Route::post('/reservation/store', [ReservationsController::class, 'store'])->name('reservation.store');
Route::get('/reservation/{id}/summary', [ReservationsController::class, 'summary'])->name('reservation.summary');
Route::post('/reservation/{id}/confirm', [ReservationsController::class, 'confirm'])->name('reservation.confirm');

// Order routes
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

// Other routes
Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');
Route::get('/upload', function () {
    return view('upload');
});

Route::post('/upload', [ImageUploadController::class, 'store'])->name('image.upload');
Route::get('/menu', [MenuController::class, 'index'])->name('menu')->middleware('auth');

Route::get('/menuadd/menu/category', [MenuCategoryController::class, 'create'])->name('menu.add.category');
Route::post('/menuadd/menu/category', [MenuCategoryController::class, 'store']);

Route::post('/menu/storeCategory', [MenuController::class, 'storeMenuCategory'])->name('menu.storeCategory');
Route::get('/menu/items/{categoryId}', [MenuController::class, 'getMenuItemsByCategory'])->name('menu.items.byCategory');

Route::get('/menu/frontend', [MenuController::class, 'frontend'])->name('menu.frontend');

Route::get('/frontend/menu', function () {
    return view('frontend.menu');
});

// Keep your existing frontend route (optional, but good to keep)
Route::get('/frontend', function () {
    return view('menu.index');
})->name('menu.index');

// Sidebar function routes
Route::post('/log-click', [LogController::class, 'logClick'])->name('log.click');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations');
Route::get('/orders', [OrderController::class, 'index'])->name('orders');
Route::get('/reports', [ReportController::class, 'index'])->name('reports');
Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Routes
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/inventory/dashboard', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');

// Inventory Routes
Route::prefix('inventory')->group(function () {
    Route::get('/stock', [InventoryController::class, 'stockIndex'])->name('inventory.stock.index');
    Route::get('/transactions', [InventoryController::class, 'transactions'])->name('inventory.transactions');
    Route::get('/expiry-report', [InventoryController::class, 'expiryReport'])->name('inventory.expiry-report');
});

// Digital Menu
Route::prefix('digital-menu')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('digital-menu.index');
});

// Reservations
Route::prefix('reservations')->group(function () {
    Route::get('/', [ReservationController::class, 'index'])->name('reservations.index');
});

// Orders
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
});

// Reports
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
});

// Customers
Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
});

// Settings
Route::prefix('settings')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Keep your existing frontend route
Route::get('/frontend', function () {
    return view('menu.index');
})->name('menu.index');

// Frontend Routes
Route::prefix('frontend')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
});

Route::get('/frontend/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/frontend/inventory', [InventoryController::class, 'index'])->name('inventory');

Route::get('/frontend/reservations', [ReservationController::class, 'index'])->name('reservations');

Route::get('/frontend/orders', [OrderController::class, 'index'])->name('orders');

Route::get('/frontend/reports', [ReportController::class, 'index'])->name('reports');

Route::get('/frontend/itemlist', [ItemController::class, 'getItemList'])->name('frontend.itemlist');

Route::get('/item-details', [ItemController::class, 'getItemDetails'])->name('item.details');

Route::middleware(['auth'])->group(function () {
    // Admin routes
    Route::prefix('admin')->middleware('can:admin-access')->group(function () {
        Route::get('/menu', [MenuController::class, 'adminIndex'])->name('admin.menu');
    });
    
    // Customer routes
    Route::get('/menu', [MenuController::class, 'customerIndex'])->name('customer.menu');
});

// Admin view (full functionality)
Route::get('/frontend', function() {
    return view('menu.admin-index');
})->name('menu.admin-index');

// Customer view (read-only)
Route::get('/frontend/customers', function() {
    return view('menu.customer-index');
});

