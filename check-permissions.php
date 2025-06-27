<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== Current Permissions for Admin Guard ===\n";
$adminPermissions = Permission::where('guard_name', 'admin')->get();
if ($adminPermissions->count() > 0) {
    foreach ($adminPermissions as $perm) {
        echo "- {$perm->name}\n";
    }
} else {
    echo "No permissions found for admin guard\n";
}

echo "\n=== Current Roles for Admin Guard ===\n";
$adminRoles = Role::where('guard_name', 'admin')->get();
if ($adminRoles->count() > 0) {
    foreach ($adminRoles as $role) {
        echo "- {$role->name}\n";
    }
} else {
    echo "No roles found for admin guard\n";
}

echo "\n=== Permissions Referenced in AdminSidebar ===\n";
// List permissions that are referenced in the sidebar component
$sidebarPermissions = [
    'inventory.view',
    'orders.view', 
    'customers.view',
    'suppliers.view',
    'reports.view',
    'organizations.view',
    'organizations.create',
    'organizations.activate',
    'branches.view',
    'branches.create', 
    'branches.activate',
    'menus.view',
    'menus.create',
    'menus.activate',
    'reservations.view',
    'users.view',
    'users.create',
    'roles.view',
    'roles.create'
];

foreach ($sidebarPermissions as $permission) {
    $exists = Permission::where('name', $permission)->where('guard_name', 'admin')->exists();
    echo ($exists ? '✅' : '❌') . " {$permission}\n";
}
