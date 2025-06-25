<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

echo "🏢 SETTING UP UNITEC ORGANIZATION FOR SUPER ADMIN\n";
echo "=================================================\n";

// Check if super admin needs an organization
$superAdmin = Admin::where('email', 'superadmin@rms.com')->first();

if (!$superAdmin) {
    echo "❌ Super admin not found\n";
    exit(1);
}

echo "✅ Super admin found: {$superAdmin->email}\n";
echo "   - Current organization: " . ($superAdmin->organization_id ? "ID {$superAdmin->organization_id}" : "NONE") . "\n";

// Check if Unitec organization exists
$unitecOrg = Organization::where('name', 'Unitec')->first();

if ($unitecOrg) {
    echo "✅ Unitec organization already exists (ID: {$unitecOrg->id})\n";
} else {
    echo "🏗️  Creating Unitec organization...\n";
    
    $unitecOrg = Organization::create([
        'name' => 'Unitec',
        'email' => 'info@unitec.com',
        'phone' => '+1-000-000-0000',
        'address' => 'Development Office',
        'description' => 'Unitec - Restaurant Management System Developer',
        'subscription_plan' => 'enterprise',
        'subscription_status' => 'active',
        'subscription_start_date' => now(),
        'subscription_end_date' => now()->addYears(10), // Long term for dev company
        'is_active' => true,
    ]);
    
    echo "✅ Unitec organization created (ID: {$unitecOrg->id})\n";
}

// Check if super admin should be assigned to Unitec
if (!$superAdmin->organization_id) {
    echo "🔗 Assigning super admin to Unitec organization...\n";
    
    $superAdmin->organization_id = $unitecOrg->id;
    $superAdmin->save();
    
    echo "✅ Super admin assigned to Unitec\n";
} else {
    echo "ℹ️  Super admin already has an organization\n";
}

// Ensure super admin has all permissions
echo "\n🔐 ENSURING SUPER ADMIN HAS ALL PERMISSIONS\n";
echo "===========================================\n";

// Get all permissions for admin guard
$allPermissions = Permission::where('guard_name', 'admin')->get();
echo "📋 Total admin permissions in system: {$allPermissions->count()}\n";

if ($allPermissions->count() > 0) {
    // Get Super Admin role for admin guard
    $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
    
    if ($superAdminRole) {
        echo "👑 Super Admin role found\n";
        
        // Sync all permissions to Super Admin role
        $superAdminRole->syncPermissions($allPermissions);
        echo "✅ All permissions assigned to Super Admin role\n";
        
        // Ensure user has the role
        if (!$superAdmin->hasRole('Super Admin')) {
            $superAdmin->assignRole('Super Admin');
            echo "✅ Super Admin role assigned to user\n";
        } else {
            echo "ℹ️  User already has Super Admin role\n";
        }
        
    } else {
        echo "❌ Super Admin role not found for admin guard\n";
    }
} else {
    echo "⚠️  No admin permissions found in system\n";
}

echo "\n📊 FINAL STATUS\n";
echo "===============\n";

// Refresh the admin data
$superAdmin->refresh();

echo "Super Admin Details:\n";
echo "   - Email: {$superAdmin->email}\n";
echo "   - Organization: " . ($superAdmin->organization ? $superAdmin->organization->name : 'NONE') . "\n";
echo "   - is_super_admin: " . ($superAdmin->is_super_admin ? 'YES' : 'NO') . "\n";
echo "   - Active: " . ($superAdmin->is_active ? 'YES' : 'NO') . "\n";
echo "   - Roles: " . $superAdmin->roles->pluck('name')->implode(', ') . "\n";
echo "   - Direct Permissions: " . $superAdmin->permissions->count() . "\n";
echo "   - All Permissions: " . $superAdmin->getAllPermissions()->count() . "\n";

echo "\nUnitec Organization:\n";
echo "   - Name: {$unitecOrg->name}\n";
echo "   - Status: " . ($unitecOrg->is_active ? 'ACTIVE' : 'INACTIVE') . "\n";
echo "   - Subscription: {$unitecOrg->subscription_plan} ({$unitecOrg->subscription_status})\n";

echo "\n🎯 RESULT: Super admin is fully configured with Unitec organization and all permissions!\n";
