<?php

// auth routes - Updated to use modern [Controller::class, 'method'] syntax
// Updated on: 2025-01-27
// Total routes: 1

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

Route::get('login', [AdminAuthController::class, 'showLoginForm'])->middleware(['web'])->name('login');
