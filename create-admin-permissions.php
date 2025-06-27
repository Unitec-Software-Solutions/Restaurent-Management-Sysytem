<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== Creating Admin Guard Permissions ===\n\n";

// Define all permissions needed for the admin guard
$permissions = [
    // Dashboard
    'dashboard.view' => 'View dashboard',
    
    // Organizations
    'organizations.view' => 'View organizations',
    'organizations.create' => 'Create organizations', 
    'organizations.edit' => 'Edit organizations',
    'organizations.delete' => 'Delete organizations',
    'organizations.activate' => 'Activate organizations',
    
    // Branches
    'branches.view' => 'View branches',
    'branches.create' => 'Create branches',
    'branches.edit' => 'Edit branches', 
    'branches.delete' => 'Delete branches',
    'branches.activate' => 'Activate branches',
    
    // Inventory
    'inventory.view' => 'View inventory',
    'inventory.create' => 'Create inventory items',
    'inventory.edit' => 'Edit inventory items',
    'inventory.delete' => 'Delete inventory items',
    'inventory.manage' => 'Manage inventory',
    
    // Orders
    'orders.view' => 'View orders',
    'orders.create' => 'Create orders',
    'orders.edit' => 'Edit orders',
    'orders.delete' => 'Delete orders',
    'orders.manage' => 'Manage orders',
    
    // Customers
    'customers.view' => 'View customers',
    'customers.create' => 'Create customers',
    'customers.edit' => 'Edit customers',
    'customers.delete' => 'Delete customers',
    
    // Suppliers
    'suppliers.view' => 'View suppliers',
    'suppliers.create' => 'Create suppliers',
    'suppliers.edit' => 'Edit suppliers',
    'suppliers.delete' => 'Delete suppliers',
    
    // Reports
    'reports.view' => 'View reports',
    'reports.create' => 'Create reports',
    'reports.export' => 'Export reports',
    
    // Menus
    'menus.view' => 'View menus',
    'menus.create' => 'Create menus',
    'menus.edit' => 'Edit menus',
    'menus.delete' => 'Delete menus',
    'menus.activate' => 'Activate menus',
    'menus.manage' => 'Manage menus',
    
    // Reservations
    'reservations.view' => 'View reservations',
    'reservations.create' => 'Create reservations',
    'reservations.edit' => 'Edit reservations',
    'reservations.delete' => 'Delete reservations',
    'reservations.manage' => 'Manage reservations',
    
    // Users
    'users.view' => 'View users',
    'users.create' => 'Create users',
    'users.edit' => 'Edit users',
    'users.delete' => 'Delete users',
    'users.manage' => 'Manage users',
    
    // Roles & Permissions
    'roles.view' => 'View roles',
    'roles.create' => 'Create roles',
    'roles.edit' => 'Edit roles',
    'roles.delete' => 'Delete roles',
    'permissions.view' => 'View permissions',
    'permissions.assign' => 'Assign permissions',
    
    // Settings
    'settings.view' => 'View settings',
    'settings.edit' => 'Edit settings',
    
    // System Administration
    'system.admin' => 'System administration',
    'system.backup' => 'System backup',
    'system.logs' => 'View system logs',
    
    // Subscription Management
    'subscriptions.view' => 'View subscriptions',
    'subscriptions.manage' => 'Manage subscriptions',
    
    // Module Management
    'modules.view' => 'View modules',
    'modules.manage' => 'Manage modules',
];

$created = 0;
$existing = 0;

foreach ($permissions as $name => $description) {
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
echo "Total: " . ($created + $existing) . " permissions\n";

// Now assign permissions to roles
echo "\n=== Assigning Permissions to Roles ===\n";

// Super Admin gets all permissions
$superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
if ($superAdminRole) {
    $allPermissions = Permission::where('guard_name', 'admin')->get();
    $superAdminRole->syncPermissions($allPermissions);
    echo "✅ Assigned all permissions to Super Admin role\n";
}

// Organization Admin gets most permissions except system admin
$orgAdminRoles = Role::where('guard_name', 'admin')
    ->where('name', 'like', '%Organization Admin%')
    ->get();

foreach ($orgAdminRoles as $role) {
    $orgPermissions = Permission::where('guard_name', 'admin')
        ->whereNotIn('name', ['system.admin', 'system.backup', 'organizations.create', 'organizations.delete'])
        ->get();
    $role->syncPermissions($orgPermissions);
    echo "✅ Assigned organization permissions to: {$role->name}\n";
}

// Basic Admin role
$adminRole = Role::where('name', 'Admin')->where('guard_name', 'admin')->first();
if ($adminRole) {
    $adminPermissions = Permission::where('guard_name', 'admin')
        ->whereIn('name', [
            'dashboard.view',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'orders.view', 'orders.create', 'orders.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'menus.view', 'menus.create', 'menus.edit',
            'reservations.view', 'reservations.create', 'reservations.edit',
            'reports.view',
            'users.view', 'users.create', 'users.edit'
        ])
        ->get();
    $adminRole->syncPermissions($adminPermissions);
    echo "✅ Assigned basic permissions to Admin role\n";
}

echo "\n=== Admin Guard Permissions Setup Complete ===\n";
