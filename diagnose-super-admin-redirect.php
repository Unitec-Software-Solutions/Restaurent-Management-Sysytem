<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 DIAGNOSING SUPER ADMIN LOGIN REDIRECT ISSUE\n";
echo "===============================================\n\n";

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AdminAuthService;

// Test 1: Check current super admin state
echo "1. CHECKING CURRENT SUPER ADMIN STATE\n";
echo "======================================\n";

$superAdmin = Admin::where('email', 'superadmin@rms.com')->first();

if ($superAdmin) {
    echo "✅ Super Admin found:\n";
    echo "   - ID: {$superAdmin->id}\n";
    echo "   - Email: {$superAdmin->email}\n";
    echo "   - Name: {$superAdmin->name}\n";
    echo "   - Organization ID: " . ($superAdmin->organization_id ?? 'NULL') . "\n";
    echo "   - is_super_admin: " . ($superAdmin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - is_active: " . ($superAdmin->is_active ? 'YES' : 'NO') . "\n";
    
    $roles = $superAdmin->roles()->pluck('name')->toArray();
    echo "   - Roles: " . (empty($roles) ? 'NONE' : implode(', ', $roles)) . "\n";
} else {
    echo "❌ Super Admin not found!\n";
    exit(1);
}

// Test 2: Check if Unitec organization exists
echo "\n2. CHECKING ORGANIZATIONS\n";
echo "=========================\n";

$organizations = Organization::all();
echo "✅ Total organizations: " . $organizations->count() . "\n";

foreach ($organizations as $org) {
    echo "   - {$org->name} (ID: {$org->id})\n";
}

$unitecOrg = Organization::where('name', 'Unitec')->first();
if ($unitecOrg) {
    echo "✅ Unitec organization exists (ID: {$unitecOrg->id})\n";
} else {
    echo "❌ Unitec organization does not exist\n";
}

// Test 3: Test login and check what happens
echo "\n3. TESTING LOGIN PROCESS\n";
echo "========================\n";

Auth::guard('admin')->logout();

$authService = new AdminAuthService();
$loginResult = $authService->login('superadmin@rms.com', 'password', false);

if ($loginResult['success']) {
    echo "✅ Login successful\n";
    echo "   - User: {$loginResult['admin']->email}\n";
    
    // Check authentication state
    $isAuth = Auth::guard('admin')->check();
    echo "   - Auth check: " . ($isAuth ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
    
    if ($isAuth) {
        $user = Auth::guard('admin')->user();
        echo "   - Current user: {$user->email}\n";
        echo "   - Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
        echo "   - is_super_admin: " . ($user->is_super_admin ? 'YES' : 'NO') . "\n";
        
        // Check roles
        $currentRoles = $user->roles()->pluck('name')->toArray();
        echo "   - Current roles: " . (empty($currentRoles) ? 'NONE' : implode(', ', $currentRoles)) . "\n";
    }
} else {
    echo "❌ Login failed: {$loginResult['error']}\n";
}

// Test 4: Check middleware requirements
echo "\n4. CHECKING MIDDLEWARE REQUIREMENTS\n";
echo "====================================\n";

if (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    
    echo "Testing middleware conditions:\n";
    
    // Check EnhancedAdminAuth conditions
    $isActiveUser = $user->is_active;
    echo "   - User is active: " . ($isActiveUser ? 'PASS' : 'FAIL') . "\n";
    
    $isAdminModel = $user instanceof \App\Models\Admin;
    echo "   - User is Admin model: " . ($isAdminModel ? 'PASS' : 'FAIL') . "\n";
    
    $hasSuperAdminRole = $user->roles()->where('name', 'Super Admin')->exists();
    echo "   - Has Super Admin role: " . ($hasSuperAdminRole ? 'PASS' : 'FAIL') . "\n";
    
    $hasOrgOrSuperAdmin = $user->organization_id || $user->is_super_admin || $hasSuperAdminRole;
    echo "   - Organization check: " . ($hasOrgOrSuperAdmin ? 'PASS' : 'FAIL') . "\n";
    echo "     - Has organization_id: " . ($user->organization_id ? 'YES' : 'NO') . "\n";
    echo "     - is_super_admin flag: " . ($user->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "     - Has Super Admin role: " . ($hasSuperAdminRole ? 'YES' : 'NO') . "\n";
    
    if (!$hasOrgOrSuperAdmin) {
        echo "   ⚠️ MIDDLEWARE WILL FAIL - User needs organization or super admin status\n";
    }
}

echo "\n5. SOLUTION ANALYSIS\n";
echo "=====================\n";

$needsOrganization = false;
$needsPermissions = false;

if ($superAdmin && !$superAdmin->organization_id && !$superAdmin->is_super_admin && !$superAdmin->roles()->where('name', 'Super Admin')->exists()) {
    $needsOrganization = true;
    echo "❌ Super admin needs either:\n";
    echo "   - An organization assignment, OR\n";
    echo "   - Proper super admin role/flag\n";
}

if ($needsOrganization) {
    echo "\n💡 RECOMMENDED SOLUTION:\n";
    echo "Create Unitec organization and assign super admin to it\n";
    echo "This will provide:\n";
    echo "   - Organization context for the super admin\n";
    echo "   - Access to all modules through organization permissions\n";
    echo "   - Proper middleware validation\n";
} else {
    echo "\n💡 CURRENT STATUS:\n";
    echo "Super admin should have sufficient permissions\n";
    echo "The redirect issue may be caused by other factors\n";
}

echo "\n🎯 DIAGNOSIS SUMMARY\n";
echo "====================\n";

$issues = [];

if ($superAdmin && !$superAdmin->organization_id && !$superAdmin->is_super_admin) {
    $issues[] = "Super admin has no organization and is_super_admin is false";
}

if ($superAdmin && !$superAdmin->roles()->where('name', 'Super Admin')->exists()) {
    $issues[] = "Super admin doesn't have Super Admin role";
}

if (!Auth::guard('admin')->check()) {
    $issues[] = "Authentication not working";
}

if (empty($issues)) {
    echo "✅ No obvious permission issues detected\n";
    echo "The redirect might be caused by route-level issues or other middleware\n";
} else {
    echo "⚠️ ISSUES DETECTED:\n";
    foreach ($issues as $issue) {
        echo "   - {$issue}\n";
    }
}

echo "\n🏁 Diagnosis completed!\n";
