<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "🚀 CREATING SUPER ADMIN WITH ALL PERMISSIONS\n";
echo "============================================\n\n";

// First, let's create all necessary permissions for the system
echo "🔐 STEP 1: Creating System Permissions\n";
echo "=====================================\n";

$allPermissions = [
    // System Administration
    'system.manage', 'system.settings', 'system.logs', 'system.backup',
    'system.admin', 'system.maintenance', 'system.monitoring',
    
    // Organization Management
    'organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete',
    'organizations.activate', 'organizations.deactivate', 'organizations.manage',
    'organizations.subscriptions', 'organizations.settings',
    
    // Branch Management
    'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
    'branches.activate', 'branches.deactivate', 'branches.manage',
    'branches.settings', 'branches.staff',
    
    // User Management
    'users.view', 'users.create', 'users.edit', 'users.delete',
    'users.manage', 'users.roles', 'users.permissions', 'users.activate',
    'users.deactivate', 'users.reset_password',
    
    // Admin Management
    'admins.view', 'admins.create', 'admins.edit', 'admins.delete',
    'admins.manage', 'admins.roles', 'admins.permissions',
    
    // Role & Permission Management
    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
    'roles.manage', 'permissions.view', 'permissions.create', 'permissions.edit',
    'permissions.delete', 'permissions.manage',
    
    // Module Management
    'modules.view', 'modules.create', 'modules.edit', 'modules.delete',
    'modules.manage', 'modules.activate', 'modules.deactivate',
    
    // Inventory Management
    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
    'inventory.manage', 'inventory.adjust', 'inventory.transfer', 'inventory.audit',
    'inventory.reports', 'inventory.export',
    
    // Menu Management
    'menus.view', 'menus.create', 'menus.edit', 'menus.delete',
    'menus.manage', 'menus.categories', 'menus.items', 'menus.pricing',
    'menus.publish', 'menus.schedule',
    
    // Order Management
    'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
    'orders.manage', 'orders.process', 'orders.cancel', 'orders.refund',
    'orders.reports', 'orders.export',
    
    // Reservation Management
    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.delete',
    'reservations.manage', 'reservations.approve', 'reservations.cancel', 'reservations.confirm',
    
    // Customer Management
    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
    'customers.manage', 'customers.history', 'customers.loyalty',
    
    // Supplier Management
    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
    'suppliers.manage', 'suppliers.payments', 'suppliers.reports',
    
    // Payment Management
    'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
    'payments.manage', 'payments.process', 'payments.refund', 'payments.reports',
    
    // Kitchen Management
    'kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.orders',
    'kitchen.status', 'kitchen.recipes', 'kitchen.production',
    
    // Staff Management
    'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
    'staff.manage', 'staff.schedules', 'staff.attendance', 'staff.performance',
    
    // Reports & Analytics
    'reports.view', 'reports.create', 'reports.edit', 'reports.delete',
    'reports.manage', 'reports.export', 'reports.schedule',
    'analytics.view', 'analytics.create', 'analytics.manage',
    
    // Financial Management
    'finance.view', 'finance.manage', 'finance.revenue', 'finance.expenses',
    'finance.taxes', 'finance.reconcile', 'finance.budgets',
    
    // Dashboard Access
    'dashboard.view', 'dashboard.manage', 'dashboard.customize',
    
    // Profile Management
    'profile.view', 'profile.edit',
    
    // Settings
    'settings.view', 'settings.edit', 'settings.manage',
    
    // Notifications
    'notifications.view', 'notifications.create', 'notifications.manage',
    'notifications.send', 'notifications.settings',
    
    // POS System
    'pos.view', 'pos.operate', 'pos.manage', 'pos.settings',
    
    // Digital Menu
    'digital_menu.view', 'digital_menu.manage', 'digital_menu.customize',
    
    // Subscriptions
    'subscriptions.view', 'subscriptions.manage', 'subscriptions.billing',
    'subscriptions.plans', 'subscriptions.upgrade',
    
    // Schedules
    'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.manage',
    
    // Tables Management
    'tables.view', 'tables.create', 'tables.edit', 'tables.manage',
    
    // Production Management
    'production.view', 'production.create', 'production.edit', 'production.manage',
    'production.orders', 'production.recipes', 'production.sessions',
    
    // Goods Transfer Notes
    'gtn.view', 'gtn.create', 'gtn.edit', 'gtn.manage', 'gtn.approve',
    
    // Purchase Orders
    'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit',
    'purchase_orders.manage', 'purchase_orders.approve',
    
    // GRN (Goods Receipt Notes)
    'grn.view', 'grn.create', 'grn.edit', 'grn.manage',
    
    // Item Transactions
    'item_transactions.view', 'item_transactions.create', 'item_transactions.edit',
    'item_transactions.manage',
    
    // Stock Management
    'stock.view', 'stock.manage', 'stock.adjust', 'stock.transfer',
    'stock.reservations', 'stock.reports',
    
    // KOT (Kitchen Order Tickets)
    'kot.view', 'kot.create', 'kot.edit', 'kot.manage',
];

$createdPermissions = 0;
$existingPermissions = 0;

foreach ($allPermissions as $permission) {
    $permissionModel = Permission::firstOrCreate([
        'name' => $permission,
        'guard_name' => 'admin'
    ]);
    
    if ($permissionModel->wasRecentlyCreated) {
        $createdPermissions++;
        echo "  ✅ Created: {$permission}\n";
    } else {
        $existingPermissions++;
    }
}

echo "\n📊 Permissions Summary:\n";
echo "  - Created: {$createdPermissions}\n";
echo "  - Existing: {$existingPermissions}\n";
echo "  - Total: " . ($createdPermissions + $existingPermissions) . "\n\n";

// Step 2: Create or update the Super Admin role
echo "👑 STEP 2: Creating Super Admin Role\n";
echo "===================================\n";

$superAdminRole = Role::firstOrCreate([
    'name' => 'Super Admin',
    'guard_name' => 'admin'
]);

// Assign ALL permissions to Super Admin role
$allPermissionsModels = Permission::where('guard_name', 'admin')->get();
$superAdminRole->syncPermissions($allPermissionsModels);

echo "  ✅ Super Admin role created/updated\n";
echo "  ✅ Assigned {$allPermissionsModels->count()} permissions to Super Admin role\n\n";

// Step 3: Create system modules
echo "📦 STEP 3: Creating System Modules\n";
echo "=================================\n";

$modules = [
    [
        'name' => 'System Administration',
        'slug' => 'system',
        'description' => 'Core system administration and settings',
        'permissions' => ['system.manage', 'system.settings', 'system.logs', 'system.backup'],
        'is_active' => true
    ],
    [
        'name' => 'Organization Management',
        'slug' => 'organizations',
        'description' => 'Multi-organization management and configuration',
        'permissions' => ['organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete'],
        'is_active' => true
    ],
    [
        'name' => 'Branch Management',
        'slug' => 'branches',
        'description' => 'Multi-branch operations and management',
        'permissions' => ['branches.view', 'branches.create', 'branches.edit', 'branches.delete'],
        'is_active' => true
    ],
    [
        'name' => 'Inventory Management',
        'slug' => 'inventory',
        'description' => 'Complete inventory tracking and management',
        'permissions' => ['inventory.view', 'inventory.create', 'inventory.edit', 'inventory.manage'],
        'is_active' => true
    ],
    [
        'name' => 'Menu Management',
        'slug' => 'menus',
        'description' => 'Menu items, categories, and pricing management',
        'permissions' => ['menus.view', 'menus.create', 'menus.edit', 'menus.manage'],
        'is_active' => true
    ],
    [
        'name' => 'Order Management',
        'slug' => 'orders',
        'description' => 'Order processing and management system',
        'permissions' => ['orders.view', 'orders.create', 'orders.edit', 'orders.manage'],
        'is_active' => true
    ],
    [
        'name' => 'Kitchen Management',
        'slug' => 'kitchen',
        'description' => 'Kitchen operations and order processing',
        'permissions' => ['kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.orders'],
        'is_active' => true
    ],
    [
        'name' => 'Staff Management',
        'slug' => 'staff',
        'description' => 'Employee management and scheduling',
        'permissions' => ['staff.view', 'staff.create', 'staff.edit', 'staff.manage'],
        'is_active' => true
    ],
    [
        'name' => 'Reports & Analytics',
        'slug' => 'reports',
        'description' => 'Business intelligence and reporting',
        'permissions' => ['reports.view', 'reports.create', 'reports.manage', 'analytics.view'],
        'is_active' => true
    ],
    [
        'name' => 'Financial Management',
        'slug' => 'finance',
        'description' => 'Financial tracking and payment processing',
        'permissions' => ['finance.view', 'finance.manage', 'payments.view', 'payments.manage'],
        'is_active' => true
    ]
];

$createdModules = 0;
$existingModules = 0;

foreach ($modules as $moduleData) {
    $module = Module::firstOrCreate(
        ['slug' => $moduleData['slug']],
        $moduleData
    );
    
    if ($module->wasRecentlyCreated) {
        $createdModules++;
        echo "  ✅ Created module: {$moduleData['name']}\n";
    } else {
        $existingModules++;
        echo "  ℹ️  Module exists: {$moduleData['name']}\n";
    }
}

echo "\n📊 Modules Summary:\n";
echo "  - Created: {$createdModules}\n";
echo "  - Existing: {$existingModules}\n";
echo "  - Total: " . ($createdModules + $existingModules) . "\n\n";

// Step 4: Get or create an organization for the super admin
echo "🏢 STEP 4: Setting up Organization\n";
echo "=================================\n";

$organization = Organization::first();
if (!$organization) {
    $organization = Organization::create([
        'name' => 'System Administration',
        'description' => 'Primary system administration organization',
        'contact_person_name' => 'System Administrator',
        'contact_person_email' => 'admin@system.rms',
        'contact_person_phone' => '+1234567890',
        'contact_person_designation' => 'System Administrator',
        'address' => 'System Headquarters',
        'city' => 'System City',
        'state' => 'System State',
        'country' => 'System Country',
        'postal_code' => '12345',
        'is_active' => true,
        'status' => 'active',
        'activated_at' => now()
    ]);
    echo "  ✅ Created organization: {$organization->name}\n";
} else {
    echo "  ℹ️  Using existing organization: {$organization->name}\n";
}

// Step 5: Create the Super Admin user
echo "\n👤 STEP 5: Creating Super Admin User\n";
echo "===================================\n";

$superAdmin = Admin::updateOrCreate(
    ['email' => 'superadmin@rms.com'],
    [
        'name' => 'Super Administrator',
        'password' => Hash::make('SuperAdmin123!'),
        'organization_id' => $organization->id,
        'is_super_admin' => true,
        'is_active' => true,
        'status' => 'active',
        'role' => 'superadmin',
        'job_title' => 'System Administrator',
        'department' => 'Administration',
        'email_verified_at' => now(),
        'preferences' => [
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => '24h',
            'currency' => 'USD',
        ],
        'ui_settings' => [
            'theme' => 'light',
            'sidebar_collapsed' => false,
            'dashboard_layout' => 'grid',
            'notifications_enabled' => true,
            'preferred_language' => 'en',
            'cards_per_row' => 4,
            'show_help_tips' => true,
        ]
    ]
);

echo "  ✅ Super Admin user created/updated\n";
echo "     Email: superadmin@rms.com\n";
echo "     Password: SuperAdmin123!\n";
echo "     Organization: {$organization->name}\n";

// Step 6: Assign Super Admin role to user
echo "\n🔗 STEP 6: Assigning Role to User\n";
echo "================================\n";

// Remove any existing roles first
$superAdmin->roles()->detach();

// Assign the Super Admin role
$superAdmin->assignRole($superAdminRole);

echo "  ✅ Super Admin role assigned to user\n";

// Step 7: Verify the setup
echo "\n✅ STEP 7: Verification\n";
echo "======================\n";

// Refresh the user data
$superAdmin->refresh();

// Check role assignment
$userRoles = $superAdmin->roles()->get();
echo "  User roles: " . $userRoles->pluck('name')->join(', ') . "\n";

// Check permissions
$userPermissions = $superAdmin->getAllPermissions();
echo "  Total permissions: {$userPermissions->count()}\n";

// Test some key methods
echo "  isSuperAdmin(): " . ($superAdmin->isSuperAdmin() ? 'YES' : 'NO') . "\n";
echo "  hasOrganizationAccess(): " . ($superAdmin->hasOrganizationAccess() ? 'YES' : 'NO') . "\n";

// Test some key permissions
$keyPermissions = [
    'system.manage',
    'organizations.view',
    'branches.view',
    'users.manage',
    'inventory.manage',
    'orders.manage'
];

echo "\n  Key Permission Tests:\n";
foreach ($keyPermissions as $permission) {
    $hasPermission = $superAdmin->hasPermissionTo($permission, 'admin');
    echo "    {$permission}: " . ($hasPermission ? '✅' : '❌') . "\n";
}

echo "\n🎉 SUPER ADMIN SETUP COMPLETE!\n";
echo "==============================\n";
echo "\n📋 LOGIN CREDENTIALS:\n";
echo "     URL: /admin/login\n";
echo "     Email: superadmin@rms.com\n";
echo "     Password: SuperAdmin123!\n";
echo "\n🔐 CAPABILITIES:\n";
echo "     - Full system administration access\n";
echo "     - Access to all organizations and branches\n";
echo "     - Complete user and role management\n";
echo "     - All module permissions\n";
echo "     - System settings and configuration\n";
echo "     - Complete inventory, menu, and order management\n";
echo "     - Financial and reporting access\n";
echo "     - Staff and kitchen management\n";
echo "\n⚠️  SECURITY NOTE:\n";
echo "     Please change the default password after first login!\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "🚀 Super Admin is ready to manage the Restaurant Management System!\n";
echo str_repeat("=", 80) . "\n";
