<?php

require_once 'vendor/autoload.php';

use App\Models\Admin;
use App\Models\Role;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 FIXING SUPER ADMIN ROLE ASSIGNMENT\n";
echo "=====================================\n\n";

// Find super admin
$superAdmin = Admin::where('email', 'superadmin@rms.com')->first();

if (!$superAdmin) {
    echo "❌ Super admin not found!\n";
    exit(1);
}

echo "✅ Found super admin: {$superAdmin->name} ({$superAdmin->email})\n";

// Find Super Admin role
$superAdminRole = Role::where('name', 'Super Admin')->where('organization_id', null)->first();

if (!$superAdminRole) {
    echo "❌ Super Admin role not found!\n";
    echo "Available roles:\n";
    $roles = Role::all();
    foreach ($roles as $role) {
        $orgInfo = $role->organization_id ? " (Org ID: {$role->organization_id})" : " (Global)";
        echo "  - {$role->name}{$orgInfo}\n";
    }
    exit(1);
}

echo "✅ Found Super Admin role: {$superAdminRole->name}\n";

// Check current roles
$currentRoles = $superAdmin->roles()->get();
echo "Current roles for super admin: ";
if ($currentRoles->count() > 0) {
    echo $currentRoles->pluck('name')->join(', ') . "\n";
} else {
    echo "NONE\n";
}

// Check if already has the role
$hasRole = $superAdmin->hasRole($superAdminRole->name, 'admin');
echo "Has Super Admin role: " . ($hasRole ? 'YES' : 'NO') . "\n";

if (!$hasRole) {
    echo "\n🔧 Assigning Super Admin role...\n";
    
    try {
        // Use Spatie's assignRole method
        $superAdmin->assignRole($superAdminRole);
        echo "✅ Role assigned successfully!\n";
        
        // Verify assignment
        $superAdmin->refresh();
        $newRoles = $superAdmin->roles()->get();
        echo "New roles: " . $newRoles->pluck('name')->join(', ') . "\n";
        
        // Test role check
        $hasRoleNow = $superAdmin->hasRole('Super Admin', 'admin');
        echo "Role check after assignment: " . ($hasRoleNow ? 'PASS' : 'FAIL') . "\n";
        
    } catch (Exception $e) {
        echo "❌ Failed to assign role: {$e->getMessage()}\n";
        
        // Try alternative method using pivot table
        echo "\n🔧 Trying direct pivot table insertion...\n";
        try {
            // Check if model_has_roles table exists and insert directly
            $superAdmin->roles()->attach($superAdminRole->id);
            echo "✅ Role attached via pivot table!\n";
            
            $superAdmin->refresh();
            $finalRoles = $superAdmin->roles()->get();
            echo "Final roles: " . $finalRoles->pluck('name')->join(', ') . "\n";
            
        } catch (Exception $e2) {
            echo "❌ Pivot table method also failed: {$e2->getMessage()}\n";
        }
    }
} else {
    echo "✅ Super admin already has the role!\n";
}

echo "\n🎯 FINAL VERIFICATION\n";
echo "=====================\n";

// Final check
$superAdmin->refresh();
$finalRoles = $superAdmin->roles()->get();
echo "Super admin roles: ";
if ($finalRoles->count() > 0) {
    echo $finalRoles->pluck('name')->join(', ') . "\n";
} else {
    echo "NONE\n";
}

$hasSuperAdminRole = $superAdmin->hasRole('Super Admin', 'admin');
echo "Has Super Admin role: " . ($hasSuperAdminRole ? 'YES' : 'NO') . "\n";

// Test authentication impact
echo "\n🔐 TESTING AUTH IMPACT\n";
echo "======================\n";

use Illuminate\Support\Facades\Auth;
use App\Services\AdminAuthService;

$authService = new AdminAuthService();
$result = $authService->login('superadmin@rms.com', 'password', false);

if ($result['success']) {
    echo "✅ Login still works\n";
    
    $authenticatedUser = Auth::guard('admin')->user();
    if ($authenticatedUser) {
        $authUserRoles = $authenticatedUser->roles()->get();
        echo "Authenticated user roles: ";
        if ($authUserRoles->count() > 0) {
            echo $authUserRoles->pluck('name')->join(', ') . "\n";
        } else {
            echo "NONE\n";
        }
        
        $hasRoleAuth = $authenticatedUser->hasRole('Super Admin', 'admin');
        echo "Auth user has Super Admin role: " . ($hasRoleAuth ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "❌ Login failed after role assignment\n";
}

echo "\n🏁 Role assignment fix completed!\n";
