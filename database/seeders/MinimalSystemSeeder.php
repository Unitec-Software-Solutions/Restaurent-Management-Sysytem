<?php
// filepath: database/seeders/MinimalSystemSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Admin;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MinimalSystemSeeder extends Seeder
{
    /**
     * Seed only essential system components for Laravel + PostgreSQL + Tailwind CSS
     */
    public function run(): void
    {
        $this->command->info('🎛️ Creating minimal system foundation...');
        
        DB::transaction(function () {
            // Step 1: Clear existing data
            $this->clearExistingData();
            
            // Step 2: Create system modules
            $this->createSystemModules();
            
            // Step 3: Create permissions
            $this->createSystemPermissions();
            
            // Step 4: Create super admin role
            $this->createSuperAdminRole();
            
            // Step 5: Create super admin user
            $this->createSuperAdmin();
        });
        
        $this->command->info('✅ Minimal system foundation created successfully');
    }

    /**
     * Clear existing data for clean start
     */
    private function clearExistingData(): void
    {
        $this->command->info('  🧹 Clearing existing data...');
        
        // Clear in dependency order for PostgreSQL
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        
        Permission::truncate();
        Role::truncate();
        Module::truncate();
        
        // Clear admins but preserve any existing data structure
        Admin::truncate();
        
        $this->command->info('  ✅ Existing data cleared');
    }

    /**
     * Create essential system modules for restaurant management
     */
    private function createSystemModules(): void
    {
        $this->command->info('  📦 Creating system modules...');
        
        $modules = [
            [
                'name' => 'Order Management',
                'slug' => 'order',
                'description' => 'Complete order processing and kitchen workflows',
                'is_active' => true
            ],
            [
                'name' => 'Reservation System',
                'slug' => 'reservation',
                'description' => 'Table booking and reservation management',
                'is_active' => true
            ],
            [
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'description' => 'Stock control and supplier management',
                'is_active' => true
            ],
            [
                'name' => 'Menu Management',
                'slug' => 'menu',
                'description' => 'Menu items, categories, and pricing',
                'is_active' => true
            ],
            [
                'name' => 'Customer Management',
                'slug' => 'customer',
                'description' => 'Customer database and loyalty programs',
                'is_active' => true
            ],
            [
                'name' => 'Kitchen Operations',
                'slug' => 'kitchen',
                'description' => 'Kitchen stations, KOT management, and production',
                'is_active' => true
            ],
            [
                'name' => 'Reports & Analytics',
                'slug' => 'report',
                'description' => 'Business intelligence and reporting',
                'is_active' => true
            ],
            [
                'name' => 'System Administration',
                'slug' => 'system',
                'description' => 'System settings and administration',
                'is_active' => true
            ]
        ];

        foreach ($modules as $moduleData) {
            $module = Module::create($moduleData);
            $this->command->info("    ✓ Module: {$module->name}");
        }
    }

    /**
     * Create essential permissions for restaurant management system
     */
    private function createSystemPermissions(): void
    {
        $this->command->info('  🔐 Creating system permissions...');
        
        $permissions = [
            // System Administration
            'system.manage', 'system.settings', 'system.backup', 'system.logs',
            
            // Order Management
            'order.view', 'order.create', 'order.update', 'order.delete', 'order.manage',
            'order.process', 'order.cancel', 'order.refund', 'order.print_kot',
            
            // Reservation Management
            'reservation.view', 'reservation.create', 'reservation.update', 'reservation.delete',
            'reservation.manage', 'reservation.approve', 'reservation.cancel', 'reservation.checkin',
            
            // Inventory Management
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete',
            'inventory.manage', 'inventory.adjust', 'inventory.transfer', 'inventory.audit',
            
            // Menu Management
            'menu.view', 'menu.create', 'menu.update', 'menu.delete', 'menu.manage',
            'menu.categories', 'menu.pricing', 'menu.schedule', 'menu.publish',
            
            // Customer Management
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'customer.manage', 'customer.loyalty', 'customer.communications',
            
            // Kitchen Operations
            'kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.orders',
            'kitchen.status', 'kitchen.recipes', 'kitchen.production',
            'kot.view', 'kot.create', 'kot.update', 'kot.manage', 'kot.print',
            
            // Reports & Analytics
            'report.view', 'report.generate', 'report.export', 'report.sales',
            'report.inventory', 'report.staff', 'report.financial', 'report.dashboard',
            
            // Organization & Branch Management
            'organization.view', 'organization.create', 'organization.update', 'organization.manage',
            'branch.view', 'branch.create', 'branch.update', 'branch.manage',
            
            // User Management
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.manage',
            'role.view', 'role.create', 'role.update', 'role.delete', 'role.manage',
            'permission.view', 'permission.manage',
            
            // Staff Management
            'staff.view', 'staff.create', 'staff.update', 'staff.delete', 'staff.manage',
            'staff.schedule', 'staff.attendance', 'staff.performance',
            
            // Financial Management
            'payment.view', 'payment.process', 'payment.refund', 'payment.manage',
            'billing.view', 'billing.create', 'billing.manage',
            
            // Dashboard & Profile
            'dashboard.view', 'dashboard.manage', 'profile.view', 'profile.update'
        ];

        $created = 0;
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
            $created++;
        }

        $this->command->info("    ✓ Created {$created} permissions");
    }

    /**
     * Create super admin role with all permissions
     */
    private function createSuperAdminRole(): void
    {
        $this->command->info('  👑 Creating super admin role...');
        
        // Create Super Admin role
        $superAdminRole = Role::create([
            'name' => 'Super Administrator',
            'guard_name' => 'admin'
        ]);

        // Assign ALL permissions to Super Admin
        $allPermissions = Permission::where('guard_name', 'admin')->get();
        $superAdminRole->syncPermissions($allPermissions);
        
        $this->command->info("    ✓ Super Admin role created with {$allPermissions->count()} permissions");
    }

    /**
     * Create super admin user
     */
    private function createSuperAdmin(): void
    {
        $this->command->info('  🔑 Creating super admin user...');
        
        // Create super admin user (system level - no organization)
        $superAdmin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@rms.com',
            'password' => Hash::make('SuperAdmin123!'),
            'phone' => '+94 11 000 0000',
            'job_title' => 'System Administrator',
            'department' => 'System Administration',
            'organization_id' => null, // System level admin
            'branch_id' => null,
            'is_super_admin' => true,
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now(),
            'preferences' => json_encode([
                'timezone' => 'UTC',
                'language' => 'en',
                'theme' => 'light',
                'notifications' => true
            ])
        ]);

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'Super Administrator')
            ->where('guard_name', 'admin')
            ->first();
            
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info('    ✓ Super Admin user created');
        $this->command->info('    📧 Email: superadmin@rms.com');
        $this->command->info('    🔒 Password: SuperAdmin123!');
        $this->command->info('    🏢 Organization: System Level (No Organization)');
        $this->command->info('    ⚡ Permissions: All System Permissions');
    }
}