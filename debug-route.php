<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Add to routes/web.php temporarily
Route::get('/debug-permissions', function() {
    if (!Auth::guard('admin')->check()) {
        return 'Not logged in as admin';
    }
    
    $admin = Auth::guard('admin')->user();
    $permissions = \App\Models\Permission::all()->pluck('name');
    
    return [
        'admin' => $admin->email,
        'all_permissions' => $permissions->toArray(),
        'user_permissions' => $admin->permissions->pluck('name')->toArray() ?? [],
        'role_permissions' => $admin->roles->flatMap->permissions->pluck('name')->toArray() ?? []
    ];
})->middleware('auth:admin');
