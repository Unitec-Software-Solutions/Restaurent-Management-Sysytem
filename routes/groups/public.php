<?php

use Illuminate\Support\Facades\Route;

// Controller imports
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\SubscriptionController;

// Sanctum CSRF
Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])->middleware(['web'])->name('sanctum.csrf-cookie');

// Folio fallback
Route::get('{fallbackPlaceholder}', function() { return view('welcome'); })->name('laravel-folio');

// Home
Route::get('/', function() { return view('welcome'); })->middleware(['web'])->name('home');

// Customer dashboard
Route::get('customer-dashboard', [CustomerDashboardController::class, 'showReservationsByPhone'])->middleware(['web', 'web'])->name('customer.dashboard');

// Public reservations (for customers)
Route::get('reservations/create', [ReservationController::class, 'create'])->middleware(['web', 'web'])->name('reservations.create');
Route::post('reservations/store', [ReservationController::class, 'store'])->middleware(['web', 'web'])->name('reservations.store');
Route::get('reservations/{reservation}/payment', [ReservationController::class, 'payment'])->middleware(['web', 'web'])->name('reservations.payment');
Route::post('reservations/{reservation}/process-payment', [ReservationController::class, 'processPayment'])->middleware(['web', 'web'])->name('reservations.process-payment');
Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->middleware(['web', 'web'])->name('reservations.confirm');
Route::get('reservations/{reservation}/summary', [ReservationController::class, 'summary'])->middleware(['web', 'web'])->name('reservations.summary');
Route::get('reservations/review', [ReservationController::class, 'review'])->middleware(['web', 'web'])->name('reservations.review');
Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->middleware(['web', 'web'])->name('reservations.cancel');
Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->middleware(['web', 'web'])->name('reservations.show');
Route::get('reservations/cancellation-success', [ReservationController::class, 'cancellationSuccess'])->middleware(['web', 'web'])->name('reservations.cancellation-success');

// Public orders
Route::get('orders', [OrderController::class, 'index'])->middleware(['web', 'web'])->name('orders.index');
Route::get('orders/all', [OrderController::class, 'allOrders'])->middleware(['web', 'web'])->name('orders.all');
Route::post('orders/update-cart', [OrderController::class, 'updateCart'])->middleware(['web', 'web'])->name('orders.update-cart');
Route::get('orders/create', [OrderController::class, 'create'])->middleware(['web', 'web'])->name('orders.create');
Route::post('orders/store', [OrderController::class, 'store'])->middleware(['web', 'web'])->name('orders.store');
Route::get('orders/{order}/summary', [OrderController::class, 'summary'])->middleware(['web', 'web'])->name('orders.summary');
Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->middleware(['web', 'web'])->name('orders.edit');
Route::delete('orders/{order}', [OrderController::class, 'destroy'])->middleware(['web', 'web'])->name('orders.destroy');
Route::put('orders/{order}', [OrderController::class, 'update'])->middleware(['web', 'web'])->name('orders.update');
Route::post('orders/check-stock', [OrderController::class, 'checkStock'])->middleware(['web', 'web'])->name('orders.check-stock');
Route::post('orders/{order}/print-kot', [OrderController::class, 'printKOT'])->middleware(['web', 'web'])->name('orders.print-kot');
Route::post('orders/{order}/print-bill', [OrderController::class, 'printBill'])->middleware(['web', 'web'])->name('orders.print-bill');
Route::post('orders/{order}/mark-preparing', [OrderController::class, 'markAsPreparing'])->middleware(['web', 'web'])->name('orders.mark-preparing');
Route::post('orders/{order}/mark-ready', [OrderController::class, 'markAsReady'])->middleware(['web', 'web'])->name('orders.mark-ready');
Route::post('orders/{order}/complete', [OrderController::class, 'completeOrder'])->middleware(['web', 'web'])->name('orders.complete');

// Takeaway orders
Route::get('orders/takeaway', [OrderController::class, 'indexTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.index');
Route::get('orders/takeaway/create', [OrderController::class, 'createTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.create');
Route::post('orders/takeaway/store', [OrderController::class, 'storeTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.store');
Route::get('orders/takeaway/{order}/edit', [OrderController::class, 'editTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.edit');
Route::get('orders/takeaway/{order}/summary', [OrderController::class, 'summary'])->middleware(['web', 'web'])->name('orders.takeaway.summary');
Route::delete('orders/takeaway/{order}/delete', [OrderController::class, 'destroyTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.destroy');
Route::put('orders/takeaway/{order}', [OrderController::class, 'updateTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.update');
Route::post('orders/takeaway/{order}/submit', [OrderController::class, 'submitTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.submit');
Route::get('orders/takeaway/{order}', [OrderController::class, 'showTakeaway'])->middleware(['web', 'web'])->name('orders.takeaway.show');

// Branch management (admin)
Route::get('branches/{branch}/summary', [BranchController::class, 'summary'])->middleware(['web', 'auth:admin'])->name('branches.summary');
Route::put('branches/{branch}/regenerate-key', [BranchController::class, 'regenerateKey'])->middleware(['web', 'auth:admin'])->name('branches.regenerate-key');

// Organization management (admin)
Route::get('organizations', [OrganizationController::class, 'index'])->middleware(['web', 'auth:admin'])->name('organizations.index');
Route::get('organizations/create', [OrganizationController::class, 'create'])->middleware(['web', 'auth:admin'])->name('organizations.create');
Route::post('organizations', [OrganizationController::class, 'store'])->middleware(['web', 'auth:admin'])->name('organizations.store');
Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->middleware(['web', 'auth:admin'])->name('organizations.edit');
Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->middleware(['web', 'auth:admin'])->name('organizations.update');
Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->middleware(['web', 'auth:admin'])->name('organizations.destroy');
Route::get('organizations/{organization}/summary', [OrganizationController::class, 'summary'])->middleware(['web', 'auth:admin'])->name('organizations.summary');
Route::put('organizations/{organization}/regenerate-key', [OrganizationController::class, 'regenerateKey'])->middleware(['web', 'auth:admin'])->name('organizations.regenerate-key');
Route::get('organizations/{organization}/branches', [BranchController::class, 'index'])->middleware(['web', 'auth:admin'])->name('branches.index');
Route::get('organizations/{organization}/branches/create', [BranchController::class, 'create'])->middleware(['web', 'auth:admin'])->name('branches.create');
Route::post('organizations/{organization}/branches', [BranchController::class, 'store'])->middleware(['web', 'auth:admin'])->name('branches.store');
Route::get('organizations/{organization}/branches/{branch}/edit', [BranchController::class, 'edit'])->middleware(['web', 'auth:admin'])->name('branches.edit');
Route::put('organizations/{organization}/branches/{branch}', [BranchController::class, 'update'])->middleware(['web', 'auth:admin'])->name('branches.update');
Route::delete('organizations/{organization}/branches/{branch}', [BranchController::class, 'destroy'])->middleware(['web', 'auth:admin'])->name('branches.destroy');
Route::get('branches', [BranchController::class, 'globalIndex'])->middleware(['web', 'auth:admin'])->name('branches.global');

// User and role management (admin)
Route::get('users', [UserController::class, 'index'])->middleware(['web', 'auth:admin'])->name('users.index');
Route::get('roles', [RoleController::class, 'index'])->middleware(['web', 'auth:admin'])->name('roles.index');

// Subscription plans (admin)
Route::get('subscription-plans', [SubscriptionPlanController::class, 'index'])->middleware(['web', 'auth:admin'])->name('subscription-plans.index');
Route::get('subscription-plans/create', [SubscriptionPlanController::class, 'create'])->middleware(['web', 'auth:admin'])->name('subscription-plans.create');
Route::post('subscription-plans', [SubscriptionPlanController::class, 'store'])->middleware(['web', 'auth:admin'])->name('subscription-plans.store');
Route::get('subscription-plans/{subscription_plan}', [SubscriptionPlanController::class, 'show'])->middleware(['web', 'auth:admin'])->name('subscription-plans.show');
Route::get('subscription-plans/{subscription_plan}/edit', [SubscriptionPlanController::class, 'edit'])->middleware(['web', 'auth:admin'])->name('subscription-plans.edit');
Route::put('subscription-plans/{subscription_plan}', [SubscriptionPlanController::class, 'update'])->middleware(['web', 'auth:admin'])->name('subscription-plans.update');
Route::delete('subscription-plans/{subscription_plan}', [SubscriptionPlanController::class, 'destroy'])->middleware(['web', 'auth:admin'])->name('subscription-plans.destroy');

// Additional public routes
Route::get('payments/process', [PaymentController::class, 'process'])->middleware(['web'])->name('payments.process');
Route::get('orders/payment', [OrderController::class, 'payment'])->middleware(['web'])->name('orders.payment');
Route::get('roles/assign', [RoleController::class, 'assign'])->middleware(['web'])->name('roles.assign');
Route::get('users/assign-role/store', [UserController::class, 'assignRoleStore'])->middleware(['web'])->name('users.assign-role.store');
Route::get('kitchen/orders/index', [KitchenController::class, 'ordersIndex'])->middleware(['web'])->name('kitchen.orders.index');
Route::get('reservations/index', [ReservationController::class, 'index'])->middleware(['web'])->name('reservations.index');
Route::get('orders/show', [OrderController::class, 'show'])->middleware(['web'])->name('orders.show');
Route::put('reservations/update', [ReservationController::class, 'update'])->middleware(['web'])->name('reservations.update');
Route::get('branch', [BranchController::class, 'index'])->middleware(['web'])->name('branch');
Route::get('organization', [OrganizationController::class, 'index'])->middleware(['web'])->name('organization');
Route::get('role', [RoleController::class, 'index'])->middleware(['web'])->name('role');

// Subscription routes
Route::get('subscription/expired', [SubscriptionController::class, 'expired'])->middleware(['web'])->name('subscription.expired');
Route::get('subscription/upgrade', [SubscriptionController::class, 'upgrade'])->middleware(['web'])->name('subscription.upgrade');
Route::get('subscription/required', [SubscriptionController::class, 'required'])->middleware(['web'])->name('subscription.required');

// Storage route
Route::get('storage/{path}', function($path) {
    return response()->file(storage_path('app/public/' . $path));
})->name('storage.local');
