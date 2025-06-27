<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== Adding Missing Admin Guard Permissions ===\n\n";

// Additional permissions that are missing
$additionalPermissions = [
    // Kitchen Operations
    'kitchen.view' => 'View kitchen operations',
    'kitchen.manage' => 'Manage kitchen operations',
    'kitchen.kots' => 'View kitchen KOTs',
    'kitchen.stations' => 'Manage kitchen stations',
    
    // Staff Management
    'staff.view' => 'View staff',
    'staff.create' => 'Create staff',
    'staff.edit' => 'Edit staff',
    'staff.delete' => 'Delete staff',
    'staff.manage' => 'Manage staff',
    
    // Scheduling
    'schedules.view' => 'View schedules',
    'schedules.create' => 'Create schedules',
    'schedules.edit' => 'Edit schedules',
    'schedules.delete' => 'Delete schedules',
    'schedules.manage' => 'Manage schedules',
    
    // Digital Menu
    'digital_menu.view' => 'View digital menu',
    'digital_menu.manage' => 'Manage digital menu',
    
    // POS Operations
    'pos.view' => 'View POS',
    'pos.operate' => 'Operate POS',
    'pos.manage' => 'Manage POS',
    
    // Analytics & Business Intelligence
    'analytics.view' => 'View analytics',
    'analytics.export' => 'Export analytics',
    
    // Finance & Accounting
    'finance.view' => 'View financial data',
    'finance.manage' => 'Manage financial data',
    
    // Marketing
    'marketing.view' => 'View marketing',
    'marketing.manage' => 'Manage marketing campaigns',
    
    // Notifications
    'notifications.view' => 'View notifications',
    'notifications.manage' => 'Manage notifications',
    
    // Audit & Compliance
    'audit.view' => 'View audit logs',
    'compliance.view' => 'View compliance reports',
];

$created = 0;
$existing = 0;

foreach ($additionalPermissions as $name => $description) {
    $permission = Permission::firstOrCreate([
        'name' => $name,
        'guard_name' => 'admin'
    ]);
    
    if ($permission->wasRecentlyCreated) {
        echo "✅ Created: {$name}\n";
        $created++;
    } else {
        echo "ℹ️  Exists: {$name}\n";
        $existing++;
    }
}

echo "\n=== Summary ===\n";
echo "Created: {$created} permissions\n";
echo "Existing: {$existing} permissions\n";

// Update role permissions
echo "\n=== Updating Role Permissions ===\n";

// Super Admin gets all permissions
$superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
if ($superAdminRole) {
    $allPermissions = Permission::where('guard_name', 'admin')->get();
    $superAdminRole->syncPermissions($allPermissions);
    echo "✅ Updated Super Admin role with all permissions\n";
}

// Organization Admin gets operational permissions
$orgAdminRoles = Role::where('guard_name', 'admin')
    ->where('name', 'like', '%Organization Admin%')
    ->get();

foreach ($orgAdminRoles as $role) {
    // Get current permissions plus new operational ones
    $currentPermissions = $role->permissions->pluck('name')->toArray();
    $additionalOperationalPermissions = [
        'kitchen.view', 'kitchen.manage',
        'staff.view', 'staff.create', 'staff.edit',
        'schedules.view', 'schedules.create', 'schedules.edit',
        'digital_menu.view', 'digital_menu.manage',
        'pos.view', 'pos.operate',
        'analytics.view',
        'notifications.view', 'notifications.manage'
    ];
    
    $newPermissions = array_unique(array_merge($currentPermissions, $additionalOperationalPermissions));
    $permissions = Permission::where('guard_name', 'admin')
        ->whereIn('name', $newPermissions)
        ->get();
    
    $role->syncPermissions($permissions);
    echo "✅ Updated {$role->name} with operational permissions\n";
}

// Admin role gets basic operational permissions
$adminRole = Role::where('name', 'Admin')->where('guard_name', 'admin')->first();
if ($adminRole) {
    $currentPermissions = $adminRole->permissions->pluck('name')->toArray();
    $additionalBasicPermissions = [
        'kitchen.view',
        'staff.view',
        'schedules.view',
        'digital_menu.view',
        'pos.view', 'pos.operate',
        'notifications.view'
    ];
    
    $newPermissions = array_unique(array_merge($currentPermissions, $additionalBasicPermissions));
    $permissions = Permission::where('guard_name', 'admin')
        ->whereIn('name', $newPermissions)
        ->get();
    
    $adminRole->syncPermissions($permissions);
    echo "✅ Updated Admin role with basic operational permissions\n";
}

echo "\n=== Total Admin Guard Permissions ===\n";
$totalPermissions = Permission::where('guard_name', 'admin')->count();
echo "Total permissions: {$totalPermissions}\n";

echo "\n=== Permission Categories ===\n";
$categories = [
    'dashboard' => 'Dashboard & Overview',
    'organizations' => 'Organization Management',
    'branches' => 'Branch Management', 
    'inventory' => 'Inventory Management',
    'orders' => 'Order Management',
    'customers' => 'Customer Management',
    'suppliers' => 'Supplier Management',
    'reports' => 'Reporting',
    'menus' => 'Menu Management',
    'reservations' => 'Reservation Management',
    'users' => 'User Management',
    'roles' => 'Role Management',
    'permissions' => 'Permission Management',
    'settings' => 'System Settings',
    'system' => 'System Administration',
    'subscriptions' => 'Subscription Management',
    'modules' => 'Module Management',
    'kitchen' => 'Kitchen Operations',
    'staff' => 'Staff Management',
    'schedules' => 'Schedule Management',
    'digital_menu' => 'Digital Menu',
    'pos' => 'Point of Sale',
    'analytics' => 'Analytics',
    'finance' => 'Finance & Accounting',
    'marketing' => 'Marketing',
    'notifications' => 'Notifications',
    'audit' => 'Audit & Compliance'
];

foreach ($categories as $prefix => $description) {
    $count = Permission::where('guard_name', 'admin')
        ->where('name', 'like', $prefix . '.%')
        ->count();
    if ($count > 0) {
        echo "📋 {$description}: {$count} permissions\n";
    }
}

echo "\n=== Admin Guard Permissions Setup Complete ===\n";
