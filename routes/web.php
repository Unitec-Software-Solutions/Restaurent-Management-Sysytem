<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;

use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReservationController;
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

use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\GoodReceivedNoteController;
use App\Http\Controllers\GoodReceivedNoteItemController;
use App\Http\Controllers\InventoryTransactionController;

use App\Http\Controllers\AdminReservationController;




Route::get('/', function () {
    return redirect('/frontend');
});


Auth::routes();

Auth::routes(['register' => false, 'login' => false]);


Route::get('/home', [HomeController::class, 'index'])->name('home');

// Inventory routes with proper naming
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    // Dashboard routes
    Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [InventoryDashboardController::class, 'getTransactionHistory'])->name('transactions');
    Route::get('/expiry-report', [InventoryDashboardController::class, 'getExpiryReport'])->name('expiry-report');
    
    // Item routes
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    
    // Stock routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/store', [StockController::class, 'store'])->name('store');
        Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
        Route::put('/{stock}', [StockController::class, 'update'])->name('update');
        Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');
    });

    // Transaction routes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [InventoryTransactionController::class, 'index'])->name('index');
        Route::get('/{transaction}', [InventoryTransactionController::class, 'show'])->name('show');
    });
    
    // GRN routes 
    Route::prefix('grn')->name('grn.')->group(function () {
        Route::resource('/', GoodReceivedNoteController::class)->except(['create']);
        Route::get('/create', [GoodReceivedNoteController::class, 'create'])->name('create');
        Route::post('/{grn}/finalize', [GoodReceivedNoteController::class, 'finalize'])->name('finalize');
        
        // GRN Items routes
        Route::post('/{grn}/items', [GoodReceivedNoteItemController::class, 'store'])->name('items.store');
        Route::put('/items/{item}', [GoodReceivedNoteItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [GoodReceivedNoteItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/items/{item}/quality-check', [GoodReceivedNoteItemController::class, 'qualityCheck'])->name('items.quality-check');
    });
    
});

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
// Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
// Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');


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
Route::get('/frontend', [FrontendController::class, 'index'])->name('frontend');

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

Route::get('/menu/addmenucategory', [MenuController::class, 'showAddMenuCategoryForm'])->name('menu.addmenucategory');
Route::post('/menu/addmenucategory', [MenuController::class, 'storeMenuCategory'])->name('menu.storemenucategory');
Route::post('/menu/storeCategory', [MenuController::class, 'storeMenuCategory'])->name('menu.storeCategory');

// Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
// Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');

// Reservation Routes
Route::prefix('reservations')->name('reservations.')->group(function () {
    // Main reservation routes first
    Route::get('/create', [ReservationController::class, 'create'])->name('create');
    Route::get('/edit', [ReservationController::class, 'edit'])->name('edit');
    Route::get('/review', [ReservationController::class, 'review'])->name('review.get');
    Route::post('/review', [ReservationController::class, 'review'])->name('review');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');
    Route::post('/waitlist', [ReservationController::class, 'joinWaitlist'])->name('waitlist');
    Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');

    // Phone verification routes
    Route::get('/check-phone', [ReservationController::class, 'showPhoneCheck'])->name('check-phone-form');
    Route::post('/check-phone', [ReservationController::class, 'checkPhone'])->name('check-phone');
    Route::post('/guest', [ReservationController::class, 'proceedAsGuest'])->name('guest');
    Route::post('/user', [ReservationController::class, 'proceedAsUser'])->name('user');

    // Parameterized routes last
    Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary')->where('reservation', '[0-9]+');
    Route::post('/{reservation}/payment', [ReservationController::class, 'processPayment'])->name('payment')->where('reservation', '[0-9]+');
    Route::delete('/{reservation}', [ReservationController::class, 'cancel'])->name('cancel')->where('reservation', '[0-9]+');
    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show')->where('reservation', '[0-9]+');
    Route::get('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Reservation Management
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/pending', [AdminReservationController::class, 'pending'])->name('reservations.pending');
    Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation}/confirm', [AdminReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');
    Route::get('/reservations/{reservation}/edit', [AdminReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [AdminReservationController::class, 'update'])->name('reservations.update');
});

Route::get('/frontend/items', [ItemController::class, 'getItemList'])->name('frontend.items');
Route::get('/frontend/items/create', [ItemController::class, 'create'])->name('frontend.items.create');
Route::post('/frontend/items', [ItemController::class, 'store'])->name('frontend.items.store');
Route::get('/frontend/items/{id}/edit', [ItemController::class, 'edit'])->name('frontend.items.edit');
Route::put('/frontend/items/{id}', [ItemController::class, 'update'])->name('frontend.items.update');
Route::delete('/frontend/items/{id}', [ItemController::class, 'destroy'])->name('frontend.items.destroy');



