<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;
<<<<<<< HEAD

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

=======
>>>>>>> c02d7fb597fa15c4f8281ddcdccdaa9970142993
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\GoodReceivedNoteController;
use App\Http\Controllers\GoodReceivedNoteItemController;
use App\Http\Controllers\InventoryTransactionController;

use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemMasterController;
use App\Http\Controllers\ItemTransactionController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\MenuFrontendController;

<<<<<<< HEAD



=======
// Public routes
>>>>>>> c02d7fb597fa15c4f8281ddcdccdaa9970142993
Route::get('/', function () {
    return redirect('/frontend');
});

<<<<<<< HEAD

Auth::routes();

Auth::routes(['register' => false, 'login' => false]);

=======
Route::get('/frontend', [MenuFrontendController::class, 'index']);
Route::get('/menu', [MenuFrontendController::class, 'index']);

Auth::routes();
>>>>>>> c02d7fb597fa15c4f8281ddcdccdaa9970142993

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication (login/logout) should be outside the auth:admin middleware
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    // New logout confirmation page and action
    Route::get('/logout', [AdminAuthController::class, 'adminLogoutPage'])->name('logout.page');
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // Protected admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('reservations', AdminReservationController::class);

        // Inventory routes
        Route::prefix('inventory')->name('inventory.')->group(function () {
            // Dashboard
            // Route::get('/', [ItemMasterController::class, 'index'])->name('index');
            Route::get('/', [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

            // Inventory Item Routes
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', [ItemMasterController::class, 'index'])->name('index');
                Route::get('/create', [ItemMasterController::class, 'create'])->name('create');
                Route::post('/', [ItemMasterController::class, 'store'])->name('store');
                Route::get('/{item}', [ItemMasterController::class, 'show'])->name('show');
                Route::get('/{item}/edit', [ItemMasterController::class, 'edit'])->name('edit');
                Route::put('/{item}', [ItemMasterController::class, 'update'])->name('update');
                Route::delete('/{item}', [ItemMasterController::class, 'destroy'])->name('destroy');
            });

            //  Route::prefix('stock')->name('stock.')->group(function () {
            //      Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
            //      Route::get('/summary', [ItemTransactionController::class, 'stockSummary'])->name('summary');
            //      Route::get('/create', [ItemTransactionController::class, 'create'])->name('create');
            //      Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
            //      Route::get('/{item}/history', [ItemTransactionController::class, 'stockHistory'])->name('history');
            //      Route::get('/movement-report', [ItemTransactionController::class, 'stockMovementReport'])->name('movement-report');
            //  });

            // Categories routes
            Route::resource('categories', ItemCategoryController::class);
        });
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
<<<<<<< HEAD

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

=======
>>>>>>> c02d7fb597fa15c4f8281ddcdccdaa9970142993
// Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
// Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');

// Reservation Routes
Route::prefix('reservations')->name('reservations.')->group(function () {
    // Main reservation routes
    Route::get('/create', [ReservationController::class, 'create'])->name('create');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');
    Route::get('/edit', [ReservationController::class, 'edit'])->name('edit');
    Route::get('/review', [ReservationController::class, 'review'])->name('review.get');
    Route::post('/review', [ReservationController::class, 'review'])->name('review');
    Route::get('/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->name('cancellation-success');
    // Parameterized reservation routes
    Route::get('/{reservation}/summary', [ReservationController::class, 'summary'])->name('summary')->where('reservation', '[0-9]+');
    Route::get('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel')->where('reservation', '[0-9]+');
    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show')->where('reservation', '[0-9]+');
<<<<<<< HEAD
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



=======
    Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('confirm');
});
>>>>>>> c02d7fb597fa15c4f8281ddcdccdaa9970142993
