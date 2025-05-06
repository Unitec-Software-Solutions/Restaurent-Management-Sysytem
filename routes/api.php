<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SystemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Customer Management Routes
Route::prefix('customers')->group(function () {
    // Public routes
    Route::post('/register', [CustomerController::class, 'register']);
    Route::post('/login', [CustomerController::class, 'login']);
    Route::post('/password/reset-request', [CustomerController::class, 'requestPasswordReset']);
    Route::post('/password/reset', [CustomerController::class, 'resetPassword']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/profile', [CustomerController::class, 'updateProfile']);
        Route::put('/preferences', [CustomerController::class, 'updatePreferences']);
    });
});

// Staff Management Routes
Route::prefix('staff')->middleware('auth:sanctum')->group(function () {
    // Staff profile management
    Route::post('/', [StaffController::class, 'createStaff']);

    // Shift management
    Route::post('/shifts', [StaffController::class, 'assignShift']);
    Route::post('/shifts/clock-in', [StaffController::class, 'clockIn']);
    Route::post('/shifts/clock-out', [StaffController::class, 'clockOut']);
    Route::get('/shifts', [StaffController::class, 'getStaffShifts']);

    // Attendance management
    Route::post('/attendance', [StaffController::class, 'recordAttendance']);
    Route::get('/attendance', [StaffController::class, 'getStaffAttendance']);

    // Training management
    Route::post('/training', [StaffController::class, 'addTrainingRecord']);
});

// System Management Routes
Route::prefix('system')->middleware('auth:sanctum')->group(function () {
    // Settings management
    Route::get('/settings', [SystemController::class, 'getSettings']);
    Route::put('/settings', [SystemController::class, 'updateSetting']);

    // Payment gateway management
    Route::post('/payment-gateways', [SystemController::class, 'configurePaymentGateway']);

    // Notification provider management
    Route::post('/notification-providers', [SystemController::class, 'configureNotificationProvider']);

    // Audit logs
    Route::get('/audit-logs', [SystemController::class, 'getAuditLogs']);
}); 