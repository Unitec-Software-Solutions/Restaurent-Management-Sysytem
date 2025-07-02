<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Module;
use App\Models\Organization;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a super admin with all permissions for all modules
     */
    public function run(): void
    {
        $this->command->info('👑 Creating Super Admin with comprehensive permissions...');
        
        // Create system modules
        $this->createSystemModules();
        
        // Create comprehensive permissions
        $this->createPermissions();
        
        // Create roles
        $this->createRoles();
        
        // Create super admin user
        $this->createSuperAdmin();
        
        $this->command->info('✅ Super Admin setup completed successfully!');
    }

    /**
     * Create system modules for the restaurant management system
     */
    private function createSystemModules(): void
    {
        $this->command->info('📦 Creating system modules...');
        
        $modules = [
            [
                'name' => 'Organizations',
                'slug' => 'organizations',
                'description' => 'Manage organizations and their settings',
                'permissions' => [
                    'organizations.view', 'organizations.create', 'organizations.edit', 
                    'organizations.delete', 'organizations.activate', 'organizations.manage'
                ]
            ],
            [
                'name' => 'Branches',
                'slug' => 'branches',
                'description' => 'Manage branches and locations',
                'permissions' => [
                    'branches.view', 'branches.create', 'branches.edit', 
                    'branches.delete', 'branches.activate', 'branches.manage'
                ]
            ],
            [
                'name' => 'User Management',
                'slug' => 'users',
                'description' => 'Manage users, roles, and permissions',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'users.roles', 'users.permissions', 'users.manage'
                ]
            ],
            [
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'description' => 'Manage inventory, stock, and suppliers',
                'permissions' => [
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
                    'inventory.adjust', 'inventory.manage', 'inventory.audit',
                    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete'
                ]
            ],
            [
                'name' => 'Menu Management',
                'slug' => 'menus',
                'description' => 'Manage menus, items, and categories',
                'permissions' => [
                    'menus.view', 'menus.create', 'menus.edit', 'menus.delete',
                    'menus.publish', 'menus.manage', 'menu_items.view', 'menu_items.create',
                    'menu_items.edit', 'menu_items.delete', 'menu_categories.manage'
                ]
            ],
            [
                'name' => 'Order Management',
                'slug' => 'orders',
                'description' => 'Manage orders and order processing',
                'permissions' => [
                    'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                    'orders.cancel', 'orders.refund', 'orders.manage', 'orders.process'
                ]
            ],
            [
                'name' => 'Reservation Management',
                'slug' => 'reservations',
                'description' => 'Manage table reservations and bookings',
                'permissions' => [
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.delete',
                    'reservations.approve', 'reservations.cancel', 'reservations.manage'
                ]
            ],
            [
                'name' => 'Payment Processing',
                'slug' => 'payments',
                'description' => 'Handle payments and financial transactions',
                'permissions' => [
                    'payments.view', 'payments.process', 'payments.refund', 'payments.manage',
                    'financial.reports', 'financial.view'
                ]
            ],
            [
                'name' => 'Production Management',
                'slug' => 'production',
                'description' => 'Manage kitchen production and workflows',
                'permissions' => [
                    'production.view', 'production.create', 'production.edit', 'production.manage',
                    'kitchen.view', 'kitchen.manage', 'recipes.view', 'recipes.create',
                    'recipes.edit', 'recipes.delete'
                ]
            ],
            [
                'name' => 'Reports & Analytics',
                'slug' => 'reports',
                'description' => 'Generate reports and view analytics',
                'permissions' => [
                    'reports.view', 'reports.generate', 'reports.export', 'analytics.view',
                    'analytics.advanced', 'dashboard.view'
                ]
            ],
            [
                'name' => 'Staff Management',
                'slug' => 'staff',
                'description' => 'Manage staff, schedules, and shifts',
                'permissions' => [
                    'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
                    'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.manage'
                ]
            ],
            [
                'name' => 'System Administration',
                'slug' => 'system',
                'description' => 'System settings and administration',
                'permissions' => [
                    'system.manage', 'system.settings', 'system.logs', 'system.backup',
                    'system.admin', 'modules.view', 'modules.create', 'modules.edit',
                    'modules.delete', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete'
                ]
            ]
        ];

        foreach ($modules as $moduleData) {
            Module::firstOrCreate(
                ['slug' => $moduleData['slug']],
                [
                    'name' => $moduleData['name'],
                    'description' => $moduleData['description'],
                    'permissions' => $moduleData['permissions'],
                    'is_active' => true
                ]
            );
        }

        $this->command->info('  ✅ Created ' . count($modules) . ' system modules');
    }

    /**
     * Create comprehensive permissions for the admin guard
     */
    private function createPermissions(): void
    {
        $this->command->info('🔐 Creating permissions...');
        
        // Get all permissions from modules
        $allPermissions = collect();
        
        Module::all()->each(function ($module) use ($allPermissions) {
            if ($module->permissions && is_array($module->permissions)) {
                $allPermissions->push(...$module->permissions);
            }
        });

        // Add comprehensive system-wide permissions
        $additionalPermissions = [
            // System Administration
            'system.manage', 'system.settings', 'system.logs', 'system.backup',
            'system.admin', 'system.maintenance', 'system.monitoring',
            
            // Organization Management
            'organizations.activate', 'organizations.deactivate', 'organizations.subscriptions',
            'organizations.settings',
            
            // Branch Management
            'branches.activate', 'branches.deactivate', 'branches.settings', 'branches.staff',
            
            // User Management
            'users.activate', 'users.deactivate', 'users.reset_password',
            
            // Admin Management
            'admins.view', 'admins.create', 'admins.edit', 'admins.delete',
            'admins.manage', 'admins.roles', 'admins.permissions',
            
            // Role & Permission Management
            'permissions.create', 'permissions.edit', 'permissions.delete', 'permissions.manage',
            
            // Module Management
            'modules.activate', 'modules.deactivate',
            
            // Advanced Inventory
            'inventory.transfer', 'inventory.audit', 'inventory.reports', 'inventory.export',
            
            // Advanced Menu
            'menus.categories', 'menus.items', 'menus.pricing', 'menus.schedule',
            
            // Advanced Orders
            'orders.process', 'orders.cancel', 'orders.refund', 'orders.reports', 'orders.export',
            
            // Advanced Reservations
            'reservations.approve', 'reservations.cancel', 'reservations.confirm',
            
            // Customer Management
            'customers.history', 'customers.loyalty',
            
            // Supplier Management
            'suppliers.payments', 'suppliers.reports',
            
            // Payment Management
            'payments.process', 'payments.refund', 'payments.reports',
            
            // Kitchen Management
            'kitchen.stations', 'kitchen.orders', 'kitchen.status', 'kitchen.recipes', 'kitchen.production',
            
            // Staff Management
            'staff.schedules', 'staff.attendance', 'staff.performance',
            
            // Reports & Analytics
            'reports.export', 'reports.schedule', 'analytics.create', 'analytics.manage',
            
            // Financial Management
            'finance.revenue', 'finance.expenses', 'finance.taxes', 'finance.reconcile', 'finance.budgets',
            
            // Dashboard & UI
            'dashboard.view', 'dashboard.manage', 'dashboard.customize',
            'profile.view', 'profile.edit',
            'settings.view', 'settings.edit', 'settings.manage',
            'notifications.view', 'notifications.create', 'notifications.manage',
            'notifications.send', 'notifications.settings',
            
            // POS System
            'pos.view', 'pos.operate', 'pos.manage', 'pos.settings',
            
            // Digital Menu
            'digital_menu.view', 'digital_menu.manage', 'digital_menu.customize',
            
            // Subscriptions
            'subscriptions.billing', 'subscriptions.plans', 'subscriptions.upgrade',
            
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

        $allPermissions->push(...$additionalPermissions);

        // Create all permissions for admin guard
        $created = 0;
        $existing = 0;
        
        $allPermissions->unique()->each(function ($permission) use (&$created, &$existing) {
            $permissionModel = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
            
            if ($permissionModel->wasRecentlyCreated) {
                $created++;
            } else {
                $existing++;
            }
        });

        $this->command->info('  ✅ Created ' . $created . ' new permissions');
        $this->command->info('  ℹ️  Found ' . $existing . ' existing permissions');
        $this->command->info('  📊 Total permissions: ' . ($created + $existing));
    }

    /**
     * Create roles with appropriate permissions
     */
    private function createRoles(): void
    {
        $this->command->info('👥 Creating roles...');
        
        // Create Super Admin role with ALL permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'admin'
        ]);

        // Assign ALL permissions to Super Admin
        $allPermissions = Permission::where('guard_name', 'admin')->get();
        $superAdminRole->syncPermissions($allPermissions);

        // Create Organization Admin role
        $orgAdminRole = Role::firstOrCreate([
            'name' => 'Organization Admin',
            'guard_name' => 'admin'
        ]);

        // Organization Admin gets most permissions except system admin ones
        $orgAdminPermissions = Permission::where('guard_name', 'admin')
            ->whereNotIn('name', [
                'system.admin', 'system.backup', 'organizations.create', 
                'organizations.delete', 'modules.create', 'modules.edit', 'modules.delete'
            ])
            ->get();
        $orgAdminRole->syncPermissions($orgAdminPermissions);

        // Create Branch Admin role
        $branchAdminRole = Role::firstOrCreate([
            'name' => 'Branch Admin',
            'guard_name' => 'admin'
        ]);

        // Branch Admin gets operational permissions
        $branchAdminPermissions = Permission::where('guard_name', 'admin')
            ->whereIn('name', [
                'dashboard.view', 'orders.view', 'orders.create', 'orders.edit', 'orders.process',
                'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.approve',
                'inventory.view', 'inventory.adjust', 'menus.view', 'menus.edit',
                'staff.view', 'staff.edit', 'reports.view', 'payments.view', 'payments.process'
            ])
            ->get();
        $branchAdminRole->syncPermissions($branchAdminPermissions);

        // Create Staff role
        $staffRole = Role::firstOrCreate([
            'name' => 'Staff',
            'guard_name' => 'admin'
        ]);

        // Staff gets basic operational permissions
        $staffPermissions = Permission::where('guard_name', 'admin')
            ->whereIn('name', [
                'dashboard.view', 'orders.view', 'orders.create', 'reservations.view',
                'inventory.view', 'menus.view', 'profile.view', 'profile.edit'
            ])
            ->get();
        $staffRole->syncPermissions($staffPermissions);

        $this->command->info('  ✅ Created 4 roles with appropriate permissions');
    }

    /**
     * Create the super admin user
     */
    private function createSuperAdmin(): void
    {
        $this->command->info('👑 Creating Super Admin user...');
        
        // Get or create organization for the super admin
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
        }

        // Create or update super admin
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

        // Remove any existing roles first to ensure clean assignment
        $superAdmin->roles()->detach();

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')
            ->where('guard_name', 'admin')
            ->first();

        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Verify permissions
        $allPermissions = $superAdmin->getAllPermissions();
        
        $this->command->info('  ✅ Super Admin created:');
        $this->command->info('     Email: superadmin@rms.com');
        $this->command->info('     Password: SuperAdmin123!');
        $this->command->info('     Organization: ' . $organization->name);
        $this->command->info('     Permissions: ' . $allPermissions->count() . ' (All System Permissions)');
        $this->command->info('     isSuperAdmin(): ' . ($superAdmin->isSuperAdmin() ? 'YES' : 'NO'));
        
        // Test key permissions
        $keyPermissions = ['system.manage', 'organizations.view', 'users.manage', 'inventory.manage'];
        $this->command->info('     Key Permissions Test:');
        foreach ($keyPermissions as $permission) {
            $hasPermission = $superAdmin->hasPermissionTo($permission, 'admin');
            $this->command->info('       ' . $permission . ': ' . ($hasPermission ? '✅' : '❌'));
        }
        
        $this->command->info('');
        $this->command->info('🔐 LOGIN CREDENTIALS:');
        $this->command->info('   URL: /admin/login');
        $this->command->info('   Email: superadmin@rms.com');
        $this->command->info('   Password: SuperAdmin123!');
        $this->command->info('⚠️  Please change the password after first login!');
    }
}
