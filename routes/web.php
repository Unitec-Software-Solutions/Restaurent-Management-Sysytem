<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuCategoryController;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', function () {
    return redirect('/menu');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/reservation/start', [ReservationsController::class, 'start'])->name('reservation.start');
Route::post('/reservation/check-phone', [ReservationsController::class, 'checkPhone'])->name('reservation.checkPhone');
Route::get('/reservations', [ReservationsController::class, 'index']);
Route::get('/signup', [CustomerAuthController::class, 'showRegistrationForm'])->name('signup');
Route::get('/reservation/create', [ReservationsController::class, 'create'])->name('reservation.create');
Route::post('/reservation/store', [ReservationsController::class, 'store'])->name('reservation.store');
Route::get('/reservation/{id}/summary', [ReservationsController::class, 'summary'])->name('reservation.summary');
Route::post('/reservation/{id}/confirm', [ReservationsController::class, 'confirm'])->name('reservation.confirm');
Route::get('/reservation/{id}/order', [OrderController::class, 'create'])->name('reservation.order');
Route::get('/reservation/{id}/payment', [PaymentController::class, 'create'])->name('reservation.payment');
Route::get('/menu/add-menu-categories', [MenuCategoryController::class, 'create'])->name('menu-categories.create');
Route::post('/menu/add-menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');
Route::resource('menu', MenuController::class)->only(['index', 'show']);
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu-management/functions', [MenuController::class, 'showFunctions'])->name('menu.functions');
Route::get('/menu/{id}', [MenuController::class, 'show'])->where('id', '[0-9]+')->name('menu.show');
Route::resource('menu-items', MenuItemController::class);
Route::get('/menu-items/create', [MenuItemController::class, 'create'])->name('menu-items.create');
Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');

Route::prefix('admin')->group(function () {
    Route::get('/menu/create', [CategoryController::class, 'create'])->name('menu.create');
    Route::post('/menu', [CategoryController::class, 'store'])->name('menu.store');
    Route::get('/menu/{category}/edit', [CategoryController::class, 'edit'])->name('menu.edit');
    Route::put('/menu/{category}', [CategoryController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{category}', [CategoryController::class, 'destroy'])->name('menu.destroy');
    Route::post('/menu/{category}/toggle', [CategoryController::class, 'toggleVisibility'])->name('menu.toggle');
    Route::post('/menu/reorder', [CategoryController::class, 'reorder'])->name('menu.reorder');
    Route::get('/menu-categories/create', [MenuCategoryController::class, 'create'])->name('menu-categories.create');
});

Route::resource('menu-categories', MenuCategoryController::class);
Route::post('/menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

Route::prefix('menu/admin')->middleware('auth')->group(function () {
    Route::get('/functions', [MenuController::class, 'adminFunctions'])->name('menu.admin.functions');
    Route::get('/edit/{id}', [MenuController::class, 'edit'])->name('menu.admin.edit');
    Route::put('/update/{id}', [MenuController::class, 'update'])->name('menu.admin.update');
    Route::delete('/delete/{id}', [MenuController::class, 'destroy'])->name('menu.admin.delete');
});

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

