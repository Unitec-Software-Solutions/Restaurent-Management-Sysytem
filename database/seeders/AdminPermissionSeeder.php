<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds following UI/UX guidelines.
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating admin permissions and roles...');

        // Create permissions following UI/UX functional groups
        $this->createPermissions();
        
        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('  ✅ Admin permissions and roles configured successfully.');
    }

    /**
     * Create permissions following UI/UX guidelines
     */
    private function createPermissions(): void
    {
        $permissions = [
            // System Administration
            'system.manage',
            'system.settings',
            'system.logs',
            
            // Organization Management
            'organizations.view',
            'organizations.create',
            'organizations.edit',
            'organizations.delete',
            'organizations.activate',
            
            // Branch Management
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',
            'branches.activate',
            
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.roles',
            
            // Menu Management
            'menus.view',
            'menus.create',
            'menus.edit',
            'menus.delete',
            'menus.publish',
            
            // Order Management
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.cancel',
            'orders.refund',
            
            // Reservation Management
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            'reservations.approve',
            
            // Inventory Management
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.adjust',
            'inventory.transfer',
            
            // Reports and Analytics
            'reports.view',
            'reports.generate',
            'reports.export',
            'analytics.view',
            
            // Financial Management
            'payments.view',
            'payments.process',
            'payments.refund',
            'financial.reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        $this->command->info('    ✅ Created ' . count($permissions) . ' admin permissions');
    }

    /**
     * Assign permissions to roles following UI/UX access levels
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin gets all permissions
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::where('guard_name', 'admin')->get();
            $superAdminRole->syncPermissions($allPermissions);
            $this->command->info('    ✅ Super Admin role assigned all permissions');
        }

        // Organization Admin permissions
        $orgAdminRole = Role::where('name', 'Organization Admin')->where('guard_name', 'admin')->first();
        if ($orgAdminRole) {
            $orgPermissions = Permission::where('guard_name', 'admin')
                ->whereIn('name', [
                    'branches.view', 'branches.create', 'branches.edit',
                    'users.view', 'users.create', 'users.edit', 'users.roles',
                    'menus.view', 'menus.create', 'menus.edit', 'menus.publish',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.cancel',
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.approve',
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust',
                    'reports.view', 'reports.generate', 'analytics.view',
                    'payments.view', 'payments.process', 'financial.reports',
                ])
                ->get();
            $orgAdminRole->syncPermissions($orgPermissions);
            $this->command->info('    ✅ Organization Admin role assigned operational permissions');
        }

        // Branch Admin permissions
        $branchAdminRole = Role::where('name', 'Branch Admin')->where('guard_name', 'admin')->first();
        if ($branchAdminRole) {
            $branchPermissions = Permission::where('guard_name', 'admin')
                ->whereIn('name', [
                    'users.view', 'users.create', 'users.edit',
                    'menus.view', 'menus.edit',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.cancel',
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.approve',
                    'inventory.view', 'inventory.adjust',
                    'reports.view', 'analytics.view',
                    'payments.view', 'payments.process',
                ])
                ->get();
            $branchAdminRole->syncPermissions($branchPermissions);
            $this->command->info('    ✅ Branch Admin role assigned branch-level permissions');
        }
    }
}
