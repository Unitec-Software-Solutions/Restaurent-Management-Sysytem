<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ComprehensivePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating comprehensive permissions system...');

        // Create all permissions first
        $this->createAllPermissions();
        
        // Create predefined roles with appropriate permissions
        $this->createPredefinedRoles();
        
        $this->command->info('✅ Comprehensive permissions system created successfully!');
    }

    /**
     * Create all system permissions organized by function/module
     */
    private function createAllPermissions(): void
    {
        $permissionGroups = [
            // Organization Management
            'organizations' => [
                'organizations.view' => 'View organizations list and details',
                'organizations.create' => 'Create new organizations',
                'organizations.edit' => 'Edit organization details',
                'organizations.delete' => 'Delete organizations',
                'organizations.activate' => 'Activate/deactivate organizations',
                'organizations.manage' => 'Full organization management access',
            ],

            // Branch Management  
            'branches' => [
                'branches.view' => 'View branches list and details',
                'branches.create' => 'Create new branches',
                'branches.edit' => 'Edit branch details',
                'branches.delete' => 'Delete branches',
                'branches.activate' => 'Activate/deactivate branches',
                'branches.manage' => 'Full branch management access',
            ],

            // User Management
            'users' => [
                'users.view' => 'View users list and details',
                'users.create' => 'Create new users',
                'users.edit' => 'Edit user details',
                'users.delete' => 'Delete users',
                'users.activate' => 'Activate/deactivate users',
                'users.manage' => 'Full user management access',
                'users.impersonate' => 'Login as other users',
            ],

            // Roles & Permissions
            'roles' => [
                'roles.view' => 'View roles and permissions',
                'roles.create' => 'Create new roles',
                'roles.edit' => 'Edit roles and permissions',
                'roles.delete' => 'Delete roles',
                'roles.assign' => 'Assign roles to users',
                'roles.manage' => 'Full roles management access',
            ],

            // Menu Management
            'menus' => [
                'menus.view' => 'View menus and menu items',
                'menus.create' => 'Create new menus and items',
                'menus.edit' => 'Edit menus and menu items',
                'menus.delete' => 'Delete menus and items',
                'menus.activate' => 'Activate/deactivate menus',
                'menus.manage' => 'Full menu management access',
            ],

            // Order Management
            'orders' => [
                'orders.view' => 'View orders and order history',
                'orders.create' => 'Create new orders',
                'orders.edit' => 'Edit existing orders',
                'orders.delete' => 'Cancel/delete orders',
                'orders.process' => 'Process and fulfill orders',
                'orders.refund' => 'Process refunds',
                'orders.manage' => 'Full order management access',
            ],

            // Inventory Management
            'inventory' => [
                'inventory.view' => 'View inventory levels and items',
                'inventory.create' => 'Add new inventory items',
                'inventory.edit' => 'Edit inventory items and levels',
                'inventory.delete' => 'Remove inventory items',
                'inventory.adjust' => 'Adjust inventory levels',
                'inventory.manage' => 'Full inventory management access',
            ],

            // Supplier Management
            'suppliers' => [
                'suppliers.view' => 'View suppliers list and details',
                'suppliers.create' => 'Add new suppliers',
                'suppliers.edit' => 'Edit supplier information',
                'suppliers.delete' => 'Remove suppliers',
                'suppliers.manage' => 'Full supplier management access',
            ],

            // Reservation Management
            'reservations' => [
                'reservations.view' => 'View reservations',
                'reservations.create' => 'Create new reservations',
                'reservations.edit' => 'Edit existing reservations',
                'reservations.delete' => 'Cancel reservations',
                'reservations.confirm' => 'Confirm reservations',
                'reservations.manage' => 'Full reservation management access',
            ],

            // Kitchen Operations
            'kitchen' => [
                'kitchen.view' => 'View kitchen orders and KOTs',
                'kitchen.manage' => 'Manage kitchen operations',
                'kitchen.stations' => 'Manage kitchen stations',
                'kitchen.production' => 'Handle production requests',
            ],

            // Reports & Analytics
            'reports' => [
                'reports.view' => 'View basic reports',
                'reports.advanced' => 'Access advanced analytics',
                'reports.export' => 'Export reports',
                'reports.manage' => 'Full reports access',
            ],

            // Module Management
            'modules' => [
                'modules.view' => 'View available modules',
                'modules.create' => 'Create new modules',
                'modules.edit' => 'Edit module configurations',
                'modules.delete' => 'Remove modules',
                'modules.activate' => 'Activate/deactivate modules',
                'modules.manage' => 'Full module management access',
            ],

            // Subscription Management
            'subscription' => [
                'subscription.view' => 'View subscription details',
                'subscription.edit' => 'Modify subscription',
                'subscription.billing' => 'Access billing information',
                'subscription.manage' => 'Full subscription management',
            ],

            // System Settings
            'settings' => [
                'settings.view' => 'View system settings',
                'settings.edit' => 'Edit system settings',
                'settings.backup' => 'Create system backups',
                'settings.maintenance' => 'Perform system maintenance',
                'settings.manage' => 'Full settings access',
            ],

            // Staff Management
            'staff' => [
                'staff.view' => 'View staff members',
                'staff.create' => 'Add new staff',
                'staff.edit' => 'Edit staff details',
                'staff.delete' => 'Remove staff',
                'staff.schedule' => 'Manage staff schedules',
                'staff.manage' => 'Full staff management access',
            ],
        ];

        foreach ($permissionGroups as $group => $permissions) {
            $this->command->info("  Creating {$group} permissions...");
            
            foreach ($permissions as $permission => $description) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'admin',
                ]);
            }
        }
    }

    /**
     * Create predefined roles with appropriate permissions
     */
    private function createPredefinedRoles(): void
    {
        $this->command->info('  Creating predefined roles...');

        // Super Admin - Has all permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Administrator',
            'guard_name' => 'admin',
        ]);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // Organization Administrator
        $orgAdminRole = Role::firstOrCreate([
            'name' => 'Organization Administrator', 
            'guard_name' => 'admin',
        ]);
        $orgAdminPermissions = [
            // Organization management (own organization only)
            'organizations.view', 'organizations.edit',
            
            // Full branch management within organization
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete', 'branches.activate', 'branches.manage',
            
            // Full user management within organization
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate', 'users.manage',
            
            // Role management within organization
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.assign', 'roles.manage',
            
            // Menu management
            'menus.view', 'menus.create', 'menus.edit', 'menus.delete', 'menus.activate', 'menus.manage',
            
            // Order management
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.process', 'orders.refund', 'orders.manage',
            
            // Inventory management
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.adjust', 'inventory.manage',
            
            // Supplier management
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete', 'suppliers.manage',
            
            // Reservation management
            'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.delete', 'reservations.confirm', 'reservations.manage',
            
            // Kitchen operations
            'kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.production',
            
            // Reports
            'reports.view', 'reports.advanced', 'reports.export', 'reports.manage',
            
            // Staff management
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete', 'staff.schedule', 'staff.manage',
            
            // Subscription management
            'subscription.view', 'subscription.edit', 'subscription.billing', 'subscription.manage',
            
            // Settings (limited)
            'settings.view', 'settings.edit',
        ];
        $orgAdminRole->syncPermissions($orgAdminPermissions);

        // Branch Administrator
        $branchAdminRole = Role::firstOrCreate([
            'name' => 'Branch Administrator',
            'guard_name' => 'admin',
        ]);
        $branchAdminPermissions = [
            // Branch management (own branch only)
            'branches.view', 'branches.edit',
            
            // User management within branch
            'users.view', 'users.create', 'users.edit', 'users.activate',
            
            // Role management within branch (limited)
            'roles.view', 'roles.assign',
            
            // Menu management for branch
            'menus.view', 'menus.create', 'menus.edit', 'menus.activate',
            
            // Order management for branch
            'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.manage',
            
            // Inventory management for branch
            'inventory.view', 'inventory.edit', 'inventory.adjust', 'inventory.manage',
            
            // Reservation management for branch
            'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.confirm', 'reservations.manage',
            
            // Kitchen operations for branch
            'kitchen.view', 'kitchen.manage', 'kitchen.production',
            
            // Staff management for branch
            'staff.view', 'staff.create', 'staff.edit', 'staff.schedule', 'staff.manage',
            
            // Basic reports
            'reports.view', 'reports.export',
            
            // Basic settings
            'settings.view',
        ];
        $branchAdminRole->syncPermissions($branchAdminPermissions);

        // Kitchen Manager
        $kitchenManagerRole = Role::firstOrCreate([
            'name' => 'Kitchen Manager',
            'guard_name' => 'admin',
        ]);
        $kitchenManagerPermissions = [
            'kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.production',
            'orders.view', 'orders.process',
            'menus.view', 'inventory.view', 'inventory.adjust',
            'staff.view', 'staff.schedule',
            'reports.view',
        ];
        $kitchenManagerRole->syncPermissions($kitchenManagerPermissions);

        // Operations Manager
        $operationsRole = Role::firstOrCreate([
            'name' => 'Operations Manager',
            'guard_name' => 'admin',
        ]);
        $operationsPermissions = [
            'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.manage',
            'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.confirm', 'reservations.manage',
            'menus.view', 'inventory.view', 'staff.view', 'staff.schedule',
            'reports.view', 'reports.export',
        ];
        $operationsRole->syncPermissions($operationsPermissions);

        // Staff Member (Basic)
        $staffRole = Role::firstOrCreate([
            'name' => 'Staff Member',
            'guard_name' => 'admin',
        ]);
        $staffPermissions = [
            'orders.view', 'orders.create', 'orders.edit',
            'reservations.view', 'reservations.create', 'reservations.edit',
            'menus.view', 'inventory.view',
        ];
        $staffRole->syncPermissions($staffPermissions);

        $this->command->info('  ✅ Predefined roles created with appropriate permissions');
    }
}
