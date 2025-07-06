<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComprehensivePermissionSeeder extends Seeder
{
    /**
     * Define all system permissions organized by functional categories
     */
    private function getPermissionDefinitions(): array
    {
        return [
            // 1. ORGANIZATION MANAGEMENT
            'organizations' => [
                'name' => 'Organization Management',
                'icon' => 'building',
                'description' => 'Manage organizations and their settings',
                'permissions' => [
                    'organizations.view' => 'View organizations',
                    'organizations.create' => 'Create organizations',
                    'organizations.edit' => 'Edit organization details',
                    'organizations.delete' => 'Delete organizations',
                    'organizations.activate' => 'Activate/deactivate organizations',
                    'organizations.manage' => 'Full organization management',
                    'organizations.settings' => 'Manage organization settings',
                    'organizations.subscription' => 'Manage organization subscription',
                ]
            ],

            // 2. BRANCH MANAGEMENT
            'branches' => [
                'name' => 'Branch Management',
                'icon' => 'store',
                'description' => 'Manage branches and locations',
                'permissions' => [
                    'branches.view' => 'View branches',
                    'branches.create' => 'Create new branches',
                    'branches.edit' => 'Edit branch details',
                    'branches.delete' => 'Delete branches',
                    'branches.activate' => 'Activate/deactivate branches',
                    'branches.manage' => 'Full branch management',
                    'branches.settings' => 'Manage branch settings',
                    'branches.reports' => 'View branch reports',
                ]
            ],

            // 3. USER & ADMIN MANAGEMENT
            'users' => [
                'name' => 'User Management',
                'icon' => 'users',
                'description' => 'Manage users and administrators',
                'permissions' => [
                    'users.view' => 'View users',
                    'users.create' => 'Create users',
                    'users.edit' => 'Edit user details',
                    'users.delete' => 'Delete users',
                    'users.activate' => 'Activate/deactivate users',
                    'users.manage' => 'Full user management',
                    'users.permissions' => 'Manage user permissions',
                    'users.roles' => 'Assign user roles',
                ]
            ],

            // 4. ROLES & PERMISSIONS
            'roles' => [
                'name' => 'Roles & Permissions',
                'icon' => 'shield-check',
                'description' => 'Manage roles and permissions',
                'permissions' => [
                    'roles.view' => 'View roles',
                    'roles.create' => 'Create roles',
                    'roles.edit' => 'Edit roles',
                    'roles.delete' => 'Delete roles',
                    'roles.manage' => 'Full role management',
                    'roles.assign' => 'Assign roles to users',
                    'permissions.view' => 'View permissions',
                    'permissions.assign' => 'Assign permissions',
                ]
            ],

            // 5. MENU MANAGEMENT
            'menus' => [
                'name' => 'Menu Management',
                'icon' => 'book-open',
                'description' => 'Manage menus and menu items',
                'permissions' => [
                    'menus.view' => 'View menus',
                    'menus.create' => 'Create menus',
                    'menus.edit' => 'Edit menus',
                    'menus.delete' => 'Delete menus',
                    'menus.activate' => 'Activate/deactivate menus',
                    'menus.manage' => 'Full menu management',
                    'menus.categories' => 'Manage menu categories',
                    'menus.pricing' => 'Manage menu pricing',
                ]
            ],

            // 6. ORDER MANAGEMENT
            'orders' => [
                'name' => 'Order Management',
                'icon' => 'shopping-cart',
                'description' => 'Manage customer orders',
                'permissions' => [
                    'orders.view' => 'View orders',
                    'orders.create' => 'Create orders',
                    'orders.edit' => 'Edit orders',
                    'orders.delete' => 'Delete orders',
                    'orders.manage' => 'Full order management',
                    'orders.process' => 'Process orders',
                    'orders.cancel' => 'Cancel orders',
                    'orders.refund' => 'Process refunds',
                ]
            ],

            // 7. INVENTORY MANAGEMENT
            'inventory' => [
                'name' => 'Inventory Management',
                'icon' => 'package',
                'description' => 'Manage inventory and stock',
                'permissions' => [
                    'inventory.view' => 'View inventory',
                    'inventory.create' => 'Add inventory items',
                    'inventory.edit' => 'Edit inventory items',
                    'inventory.delete' => 'Delete inventory items',
                    'inventory.manage' => 'Full inventory management',
                    'inventory.adjust' => 'Adjust stock levels',
                    'inventory.alerts' => 'Manage inventory alerts',
                    'inventory.reports' => 'View inventory reports',
                ]
            ],

            // 8. SUPPLIER MANAGEMENT
            'suppliers' => [
                'name' => 'Supplier Management',
                'icon' => 'truck',
                'description' => 'Manage suppliers and vendors',
                'permissions' => [
                    'suppliers.view' => 'View suppliers',
                    'suppliers.create' => 'Create suppliers',
                    'suppliers.edit' => 'Edit supplier details',
                    'suppliers.delete' => 'Delete suppliers',
                    'suppliers.manage' => 'Full supplier management',
                    'suppliers.orders' => 'Manage supplier orders',
                    'suppliers.payments' => 'Manage supplier payments',
                    'suppliers.reports' => 'View supplier reports',
                ]
            ],

            // 9. RESERVATION MANAGEMENT
            'reservations' => [
                'name' => 'Reservation Management',
                'icon' => 'calendar',
                'description' => 'Manage table reservations',
                'permissions' => [
                    'reservations.view' => 'View reservations',
                    'reservations.create' => 'Create reservations',
                    'reservations.edit' => 'Edit reservations',
                    'reservations.delete' => 'Delete reservations',
                    'reservations.manage' => 'Full reservation management',
                    'reservations.confirm' => 'Confirm reservations',
                    'reservations.cancel' => 'Cancel reservations',
                    'reservations.reports' => 'View reservation reports',
                ]
            ],

            // 10. KITCHEN OPERATIONS
            'kitchen' => [
                'name' => 'Kitchen Operations',
                'icon' => 'chef-hat',
                'description' => 'Manage kitchen operations',
                'permissions' => [
                    'kitchen.view' => 'View kitchen operations',
                    'kitchen.manage' => 'Manage kitchen operations',
                    'kitchen.orders' => 'Manage kitchen orders',
                    'kitchen.stations' => 'Manage kitchen stations',
                    'kitchen.staff' => 'Manage kitchen staff',
                    'kitchen.reports' => 'View kitchen reports',
                    'kitchen.settings' => 'Manage kitchen settings',
                    'kitchen.inventory' => 'Manage kitchen inventory',
                ]
            ],

            // 11. REPORTS & ANALYTICS
            'reports' => [
                'name' => 'Reports & Analytics',
                'icon' => 'chart-bar',
                'description' => 'View and generate reports',
                'permissions' => [
                    'reports.view' => 'View reports',
                    'reports.create' => 'Create custom reports',
                    'reports.export' => 'Export reports',
                    'reports.manage' => 'Full report management',
                    'reports.sales' => 'View sales reports',
                    'reports.financial' => 'View financial reports',
                    'reports.inventory' => 'View inventory reports',
                    'reports.analytics' => 'View analytics dashboard',
                ]
            ],

            // 12. STAFF MANAGEMENT
            'staff' => [
                'name' => 'Staff Management',
                'icon' => 'user-group',
                'description' => 'Manage staff and employees',
                'permissions' => [
                    'staff.view' => 'View staff',
                    'staff.create' => 'Create staff accounts',
                    'staff.edit' => 'Edit staff details',
                    'staff.delete' => 'Delete staff accounts',
                    'staff.manage' => 'Full staff management',
                    'staff.schedules' => 'Manage staff schedules',
                    'staff.permissions' => 'Manage staff permissions',
                    'staff.payroll' => 'Manage staff payroll',
                ]
            ],

            // 13. SUBSCRIPTION MANAGEMENT
            'subscription' => [
                'name' => 'Subscription Management',
                'icon' => 'credit-card',
                'description' => 'Manage subscriptions and billing',
                'permissions' => [
                    'subscription.view' => 'View subscription details',
                    'subscription.edit' => 'Edit subscription',
                    'subscription.manage' => 'Full subscription management',
                    'subscription.billing' => 'Manage billing',
                    'subscription.plans' => 'Manage subscription plans',
                    'subscription.upgrade' => 'Upgrade/downgrade subscription',
                    'subscription.cancel' => 'Cancel subscription',
                    'subscription.reports' => 'View subscription reports',
                ]
            ],

            // 14. MODULE MANAGEMENT
            'modules' => [
                'name' => 'Module Management',
                'icon' => 'grid',
                'description' => 'Manage system modules',
                'permissions' => [
                    'modules.view' => 'View modules',
                    'modules.create' => 'Create modules',
                    'modules.edit' => 'Edit modules',
                    'modules.delete' => 'Delete modules',
                    'modules.manage' => 'Full module management',
                    'modules.activate' => 'Activate/deactivate modules',
                    'modules.configure' => 'Configure modules',
                    'modules.analytics' => 'View module analytics',
                ]
            ],

            // 15. SYSTEM SETTINGS
            'settings' => [
                'name' => 'System Settings',
                'icon' => 'cog',
                'description' => 'Manage system settings',
                'permissions' => [
                    'settings.view' => 'View system settings',
                    'settings.edit' => 'Edit system settings',
                    'settings.manage' => 'Full settings management',
                    'settings.security' => 'Manage security settings',
                    'settings.backup' => 'Manage system backups',
                    'settings.logs' => 'View system logs',
                    'settings.maintenance' => 'System maintenance',
                    'settings.integrations' => 'Manage integrations',
                ]
            ],

            // 16. PRODUCTION MANAGEMENT
            'production' => [
                'name' => 'Production Management',
                'icon' => 'factory',
                'description' => 'Manage production processes',
                'permissions' => [
                    'production.view' => 'View production',
                    'production.create' => 'Create production orders',
                    'production.edit' => 'Edit production orders',
                    'production.delete' => 'Delete production orders',
                    'production.manage' => 'Full production management',
                    'production.schedule' => 'Schedule production',
                    'production.track' => 'Track production progress',
                    'production.reports' => 'View production reports',
                ]
            ],

            // 17. DASHBOARD & GENERAL
            'dashboard' => [
                'name' => 'Dashboard Access',
                'icon' => 'gauge',
                'description' => 'Dashboard and general access',
                'permissions' => [
                    'dashboard.view' => 'View dashboard',
                    'dashboard.manage' => 'Manage dashboard layout',
                    'dashboard.widgets' => 'Manage dashboard widgets',
                    'dashboard.export' => 'Export dashboard data',
                ]
            ],
        ];
    }

    /**
     * Define predefined role templates with permissions
     */
    private function getRoleTemplates(): array
    {
        return [
            'Super Administrator' => [
                'guard_name' => 'admin',
                'level' => 'system',
                'description' => 'Full system access and control',
                'icon' => 'shield',
                'color' => 'red',
                'permissions' => 'all', // Special case - gets all permissions
            ],

            'Organization Administrator' => [
                'guard_name' => 'admin',
                'level' => 'organization',
                'description' => 'Full access within organization scope',
                'icon' => 'building',
                'color' => 'purple',
                'permissions' => [
                    // Organization Management (All)
                    'organizations.view', 'organizations.edit', 'organizations.settings', 'organizations.subscription',
                    
                    // Branch Management (All except create/delete - those might be subscription limited)
                    'branches.view', 'branches.create', 'branches.edit', 'branches.activate', 'branches.manage', 'branches.settings', 'branches.reports',
                    
                    // User Management (All within org)
                    'users.view', 'users.create', 'users.edit', 'users.activate', 'users.manage', 'users.permissions', 'users.roles',
                    
                    // Roles & Permissions (Within org scope)
                    'roles.view', 'roles.create', 'roles.edit', 'roles.manage', 'roles.assign', 'permissions.view', 'permissions.assign',
                    
                    // Menu Management (All)
                    'menus.view', 'menus.create', 'menus.edit', 'menus.activate', 'menus.manage', 'menus.categories', 'menus.pricing',
                    
                    // Order Management (All)
                    'orders.view', 'orders.create', 'orders.edit', 'orders.manage', 'orders.process', 'orders.cancel', 'orders.refund',
                    
                    // Inventory Management (All)
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.manage', 'inventory.adjust', 'inventory.alerts', 'inventory.reports',
                    
                    // Supplier Management (All)
                    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.manage', 'suppliers.orders', 'suppliers.payments', 'suppliers.reports',
                    
                    // Reservation Management (All)
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.manage', 'reservations.confirm', 'reservations.cancel', 'reservations.reports',
                    
                    // Kitchen Operations (All)
                    'kitchen.view', 'kitchen.manage', 'kitchen.orders', 'kitchen.stations', 'kitchen.staff', 'kitchen.reports', 'kitchen.settings', 'kitchen.inventory',
                    
                    // Reports & Analytics (All)
                    'reports.view', 'reports.create', 'reports.export', 'reports.manage', 'reports.sales', 'reports.financial', 'reports.inventory', 'reports.analytics',
                    
                    // Staff Management (All)
                    'staff.view', 'staff.create', 'staff.edit', 'staff.manage', 'staff.schedules', 'staff.permissions', 'staff.payroll',
                    
                    // Subscription Management (View and manage own)
                    'subscription.view', 'subscription.edit', 'subscription.billing', 'subscription.upgrade', 'subscription.reports',
                    
                    // Production Management (All)
                    'production.view', 'production.create', 'production.edit', 'production.manage', 'production.schedule', 'production.track', 'production.reports',
                    
                    // Dashboard
                    'dashboard.view', 'dashboard.manage', 'dashboard.widgets', 'dashboard.export',
                ]
            ],

            'Branch Administrator' => [
                'guard_name' => 'admin',
                'level' => 'branch',
                'description' => 'Full access within branch scope',
                'icon' => 'store',
                'color' => 'green',
                'permissions' => [
                    // Branch Management (Own branch only)
                    'branches.view', 'branches.edit', 'branches.settings', 'branches.reports',
                    
                    // User Management (Branch level)
                    'users.view', 'users.create', 'users.edit', 'users.activate', 'users.roles',
                    
                    // Roles & Permissions (Branch level)
                    'roles.view', 'roles.assign', 'permissions.view',
                    
                    // Menu Management (Branch specific)
                    'menus.view', 'menus.create', 'menus.edit', 'menus.activate', 'menus.categories', 'menus.pricing',
                    
                    // Order Management (All)
                    'orders.view', 'orders.create', 'orders.edit', 'orders.manage', 'orders.process', 'orders.cancel', 'orders.refund',
                    
                    // Inventory Management (Branch specific)
                    'inventory.view', 'inventory.edit', 'inventory.manage', 'inventory.adjust', 'inventory.alerts', 'inventory.reports',
                    
                    // Supplier Management (View and basic management)
                    'suppliers.view', 'suppliers.edit', 'suppliers.orders',
                    
                    // Reservation Management (All)
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.manage', 'reservations.confirm', 'reservations.cancel', 'reservations.reports',
                    
                    // Kitchen Operations (All)
                    'kitchen.view', 'kitchen.manage', 'kitchen.orders', 'kitchen.stations', 'kitchen.staff', 'kitchen.reports', 'kitchen.settings', 'kitchen.inventory',
                    
                    // Reports & Analytics (Branch specific)
                    'reports.view', 'reports.export', 'reports.sales', 'reports.inventory', 'reports.analytics',
                    
                    // Staff Management (Branch level)
                    'staff.view', 'staff.create', 'staff.edit', 'staff.manage', 'staff.schedules', 'staff.permissions',
                    
                    // Production Management (Branch specific)
                    'production.view', 'production.create', 'production.edit', 'production.manage', 'production.schedule', 'production.track', 'production.reports',
                    
                    // Dashboard
                    'dashboard.view', 'dashboard.manage', 'dashboard.widgets',
                ]
            ],

            // NOTE: Noise roles like Kitchen Manager, Operations Manager, Staff Member 
            // are no longer created automatically. Only essential admin roles are created.
            // If you need these operational roles, create them manually through the admin interface.
            
            /*
            'Kitchen Manager' => [
                'guard_name' => 'admin',
                'level' => 'branch',
                'description' => 'Kitchen operations and order processing',
                'icon' => 'chef-hat',
                'color' => 'orange',
                'permissions' => [
                    // Kitchen Operations (All)
                    'kitchen.view', 'kitchen.manage', 'kitchen.orders', 'kitchen.stations', 'kitchen.staff', 'kitchen.reports', 'kitchen.settings', 'kitchen.inventory',
                    
                    // Order Management (Kitchen related)
                    'orders.view', 'orders.process',
                    
                    // Menu Management (View and input)
                    'menus.view', 'menus.edit',
                    
                    // Inventory Management (Kitchen items)
                    'inventory.view', 'inventory.adjust', 'inventory.alerts',
                    
                    // Staff Management (Kitchen staff)
                    'staff.view', 'staff.schedules',
                    
                    // Production Management
                    'production.view', 'production.create', 'production.edit', 'production.manage', 'production.schedule', 'production.track',
                    
                    // Reports (Kitchen related)
                    'reports.view', 'reports.inventory',
                    
                    // Dashboard
                    'dashboard.view',
                ]
            ],

            'Operations Manager' => [
                'guard_name' => 'admin',
                'level' => 'branch',
                'description' => 'Daily operations and staff management',
                'icon' => 'clipboard-check',
                'color' => 'blue',
                'permissions' => [
                    // Order Management
                    'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.cancel',
                    
                    // Reservation Management
                    'reservations.view', 'reservations.create', 'reservations.edit', 'reservations.confirm', 'reservations.cancel',
                    
                    // Staff Management
                    'staff.view', 'staff.create', 'staff.edit', 'staff.schedules',
                    
                    // Inventory Management (Basic)
                    'inventory.view', 'inventory.adjust',
                    
                    // Menu Management (View)
                    'menus.view',
                    
                    // Reports (Operational)
                    'reports.view', 'reports.sales',
                    
                    // Dashboard
                    'dashboard.view',
                ]
            ],

            'Staff Member' => [
                'guard_name' => 'admin',
                'level' => 'staff',
                'description' => 'Basic operational access',
                'icon' => 'user',
                'color' => 'gray',
                'permissions' => [
                    // Basic Order Management
                    'orders.view', 'orders.create',
                    
                    // Basic Reservation Management
                    'reservations.view', 'reservations.create',
                    
                    // Menu View
                    'menus.view',
                    
                    // Dashboard
                    'dashboard.view',
                ]
            ],
            */
        ];
    }

    /**
     * Run the permission seeder
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating comprehensive permission system...');

        DB::beginTransaction();
        try {
            // Step 1: Create all permissions
            $this->createPermissions();
            
            // Step 2: Create predefined roles
            $this->createRoles();
            
            // Step 3: Assign permissions to roles
            $this->assignPermissionsToRoles();

            DB::commit();
            $this->command->info('✅ Comprehensive permission system created successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to create permission system: ' . $e->getMessage());
            Log::error('Permission seeder failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * Create all permissions from definitions
     */
    private function createPermissions(): void
    {
        $this->command->info('  📋 Creating permissions...');
        
        $definitions = $this->getPermissionDefinitions();
        $created = 0;

        foreach ($definitions as $category => $data) {
            foreach ($data['permissions'] as $permission => $description) {
                $existing = Permission::where('name', $permission)
                    ->where('guard_name', 'admin')
                    ->first();
                
                if (!$existing) {
                    Permission::create([
                        'name' => $permission,
                        'guard_name' => 'admin'
                    ]);
                    $created++;
                }
            }
        }

        $this->command->info("    ✓ Created {$created} permissions");
    }

    /**
     * Create predefined roles
     */
    private function createRoles(): void
    {
        $this->command->info('  👥 Creating predefined roles...');
        
        $templates = $this->getRoleTemplates();
        $created = 0;

        foreach ($templates as $roleName => $data) {
            $existing = Role::where('name', $roleName)
                ->where('guard_name', $data['guard_name'])
                ->first();
                
            if (!$existing) {
                Role::create([
                    'name' => $roleName,
                    'guard_name' => $data['guard_name']
                ]);
                $created++;
            }
        }

        $this->command->info("    ✓ Created {$created} roles");
    }

    /**
     * Assign permissions to roles based on templates
     */
    private function assignPermissionsToRoles(): void
    {
        $this->command->info('  🔗 Assigning permissions to roles...');
        
        $templates = $this->getRoleTemplates();

        foreach ($templates as $roleName => $data) {
            $role = Role::where('name', $roleName)->where('guard_name', $data['guard_name'])->first();
            
            if (!$role) {
                continue;
            }

            if ($data['permissions'] === 'all') {
                // Super Admin gets all permissions
                $allPermissions = Permission::where('guard_name', 'admin')->pluck('name')->toArray();
                $role->syncPermissions($allPermissions);
                $this->command->info("    ✓ Assigned ALL permissions to {$roleName}");
            } else {
                // Assign specific permissions
                $validPermissions = Permission::where('guard_name', 'admin')
                    ->whereIn('name', $data['permissions'])
                    ->pluck('name')
                    ->toArray();
                
                $role->syncPermissions($validPermissions);
                $this->command->info("    ✓ Assigned " . count($validPermissions) . " permissions to {$roleName}");
            }
        }
    }

    /**
     * Get permission definitions for view/controller use
     */
    public static function getPermissionCategories(): array
    {
        $instance = new self();
        return $instance->getPermissionDefinitions();
    }

    /**
     * Get role templates for view/controller use
     */
    public static function getRoleDefinitions(): array
    {
        $instance = new self();
        return $instance->getRoleTemplates();
    }
}
