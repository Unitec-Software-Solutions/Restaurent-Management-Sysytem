<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationWorkflowController;

/*
|--------------------------------------------------------------------------
| Reservation and Order Workflow Routes
|--------------------------------------------------------------------------
*/

// Customer reservation workflow
Route::prefix('reservations')->group(function () {
    Route::get('{reservation}/summary', [ReservationWorkflowController::class, 'showReservationSummary'])
        ->name('reservations.summary');
    
    Route::post('{reservation}/confirm', [ReservationWorkflowController::class, 'confirmReservation'])
        ->name('reservations.confirm');
    
    Route::get('{reservation}/create-order', [ReservationWorkflowController::class, 'createOrderFromReservation'])
        ->name('reservations.create-order');
    
    Route::post('{reservation}/store-order', [ReservationWorkflowController::class, 'storeOrderFromReservation'])
        ->name('reservations.store-order');
});

// Takeaway order routes
Route::prefix('orders/takeaway')->group(function () {
    Route::get('create', [ReservationWorkflowController::class, 'createTakeawayOrder'])
        ->name('orders.takeaway.create');
    
    Route::post('store', [ReservationWorkflowController::class, 'storeTakeawayOrder'])
        ->name('orders.takeaway.store');
    
    Route::get('{order}/summary', [ReservationWorkflowController::class, 'showOrderSummary'])
        ->name('orders.takeaway.summary');
});

// Order management routes
Route::prefix('orders')->group(function () {
    Route::get('{order}/summary', [ReservationWorkflowController::class, 'showOrderSummary'])
        ->name('orders.summary');
    
    Route::get('{order}/print-kot', [ReservationWorkflowController::class, 'printKOT'])
        ->name('orders.print-kot');
});

// AJAX routes
Route::get('api/menu-items/branch/{branchId}', [ReservationWorkflowController::class, 'getMenuItemsForBranch'])
    ->name('api.menu-items.branch');

Route::get('api/branches/organization/{organizationId}', [ReservationWorkflowController::class, 'getBranchesForOrganization'])
    ->name('api.branches.organization');

Route::get('api/organizations/{organizationId}/branches', [ReservationWorkflowController::class, 'getBranchesForOrganization'])
    ->name('api.organizations.branches');

Route::get('api/admin/defaults', [ReservationWorkflowController::class, 'getAdminDefaults'])
    ->name('api.admin.defaults');

// Admin specific routes
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('today', [ReservationWorkflowController::class, 'getTodayOrders'])
            ->name('admin.orders.today');
        
        Route::get('create-with-defaults', [ReservationWorkflowController::class, 'adminCreateOrder'])
            ->name('admin.orders.create-with-defaults');
        
        Route::get('{order}/check-kot', [ReservationWorkflowController::class, 'checkKOTItems'])
            ->name('admin.orders.check-kot');
        
        Route::get('{order}/print-kot', [ReservationWorkflowController::class, 'printKOT'])
            ->name('admin.orders.print-kot');
    });
});
