<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;

echo "🔍 VERIFYING SUPER ADMIN CREATION\n";
echo "=================================\n\n";

$admin = Admin::where('email', 'superadmin@rms.com')->first();

if (!$admin) {
    echo "❌ Super admin not found!\n";
    exit(1);
}

echo "✅ Super Admin Found!\n\n";
echo "📋 ADMIN DETAILS:\n";
echo "   Name: {$admin->name}\n";
echo "   Email: {$admin->email}\n";
echo "   Is Super Admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
echo "   Is Active: " . ($admin->is_active ? 'YES' : 'NO') . "\n";
echo "   Status: {$admin->status}\n";
echo "   Organization: " . ($admin->organization ? $admin->organization->name : 'None') . "\n";
echo "   Job Title: {$admin->job_title}\n";
echo "   Department: {$admin->department}\n\n";

echo "🔐 PERMISSIONS & ROLES:\n";
echo "   Total Permissions: " . $admin->getAllPermissions()->count() . "\n";
echo "   Roles: " . $admin->roles->pluck('name')->join(', ') . "\n\n";

echo "🧪 METHOD TESTS:\n";
echo "   isSuperAdmin(): " . ($admin->isSuperAdmin() ? '✅ YES' : '❌ NO') . "\n";
echo "   hasOrganizationAccess(): " . ($admin->hasOrganizationAccess() ? '✅ YES' : '❌ NO') . "\n";
echo "   canManageAdmins(): " . ($admin->canManageAdmins() ? '✅ YES' : '❌ NO') . "\n";
echo "   canManageSystem(): " . ($admin->canManageSystem() ? '✅ YES' : '❌ NO') . "\n\n";

echo "🔑 KEY PERMISSION TESTS:\n";
$keyPermissions = [
    'system.manage' => 'System Management',
    'organizations.view' => 'Organization Access',
    'organizations.create' => 'Create Organizations',
    'users.manage' => 'User Management',
    'admins.manage' => 'Admin Management',
    'inventory.manage' => 'Inventory Management',
    'orders.manage' => 'Order Management',
    'kitchen.manage' => 'Kitchen Management',
    'finance.manage' => 'Financial Management',
    'reports.manage' => 'Reports Management'
];

foreach ($keyPermissions as $permission => $description) {
    $hasPermission = $admin->hasPermissionTo($permission, 'admin');
    echo "   {$description}: " . ($hasPermission ? '✅' : '❌') . "\n";
}

echo "\n🎯 SUMMARY:\n";
echo "============\n";
if ($admin->isSuperAdmin() && 
    $admin->hasOrganizationAccess() && 
    $admin->canManageAdmins() && 
    $admin->canManageSystem()) {
    echo "🎉 SUCCESS! Super Admin is fully configured with all permissions!\n";
    echo "\n📝 LOGIN INSTRUCTIONS:\n";
    echo "   1. Navigate to: /admin/login\n";
    echo "   2. Email: superadmin@rms.com\n";
    echo "   3. Password: SuperAdmin123!\n";
    echo "   4. ⚠️  IMPORTANT: Change password after first login!\n";
} else {
    echo "❌ ISSUE: Super Admin is not fully configured!\n";
    echo "   Please check the configuration and try again.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔒 Super Admin Verification Complete\n";
echo str_repeat("=", 60) . "\n";
