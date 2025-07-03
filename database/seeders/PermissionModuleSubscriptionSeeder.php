<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionModuleSubscriptionSeeder extends Seeder
{
    /**
     * Seed the system modules, permissions, and subscription plans
     */
    public function run(): void
    {
        $this->command->info('🎛️ Creating Permission & Subscription Foundation...');
        
        DB::transaction(function () {
            // Clear existing data for clean start
            $this->clearExistingData();
            
            // Create system modules
            $this->createSystemModules();
            
            // Create granular permissions
            $this->createGranularPermissions();
            
            // Create tiered subscription plans
            $this->createTieredSubscriptionPlans();
            
            // Create system roles
            $this->createSystemRoles();
        });
        
        $this->command->info('✅ Permission & Subscription Foundation created successfully');
    }

    private function clearExistingData(): void
    {
        // Clear in dependency order
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        
        Permission::truncate();
        Role::truncate();
        Module::truncate();
        SubscriptionPlan::truncate();
        SubscriptionPlan::truncate();
        
        $this->command->info('  🧹 Cleared existing permission and subscription data');
    }

    private function createSystemModules(): void
    {
        $modules = [
            // Core Operations
            [
                'name' => 'Order Management',
                'slug' => 'order',
                'description' => 'Complete order processing system',
                'permissions' => [
                    'order.manage', 'order.view', 'order.create', 'order.update', 'order.delete',
                    'order.process', 'order.cancel', 'order.refund', 'order.print_kot'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Reservation System',
                'slug' => 'reservation',
                'description' => 'Table reservation and booking management',
                'permissions' => [
                    'reservation.manage', 'reservation.view', 'reservation.create', 'reservation.update',
                    'reservation.approve', 'reservation.cancel', 'reservation.checkin'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'description' => 'Stock control and supplier management',
                'permissions' => [
                    'inventory.manage', 'inventory.view', 'inventory.adjust', 'inventory.transfer',
                    'inventory.audit', 'inventory.suppliers', 'inventory.purchase_orders'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Customer Management',
                'slug' => 'customer',
                'description' => 'Customer database and loyalty programs',
                'permissions' => [
                    'customer.manage', 'customer.view', 'customer.create', 'customer.update',
                    'customer.loyalty', 'customer.communications'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Menu Management',
                'slug' => 'menu',
                'description' => 'Menu items, categories, and pricing',
                'permissions' => [
                    'menu.manage', 'menu.view', 'menu.create', 'menu.update', 'menu.delete',
                    'menu.categories', 'menu.pricing', 'menu.schedule'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Reports & Analytics',
                'slug' => 'report',
                'description' => 'Business intelligence and reporting',
                'permissions' => [
                    'report.view', 'report.sales', 'report.inventory', 'report.staff',
                    'report.financial', 'report.export', 'report.dashboard'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Supplier Management',
                'slug' => 'supplier',
                'description' => 'Vendor relationships and procurement',
                'permissions' => [
                    'supplier.manage', 'supplier.view', 'supplier.create', 'supplier.update',
                    'supplier.orders', 'supplier.payments', 'supplier.performance'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Production & Kitchen',
                'slug' => 'production',
                'description' => 'Kitchen operations and food production',
                'permissions' => [
                    'production.manage', 'production.view', 'production.stations',
                    'production.workflow', 'production.quality_control'
                ],
                'is_active' => true
            ]
        ];

        foreach ($modules as $moduleData) {
            $module = Module::create([
                'name' => $moduleData['name'],
                'slug' => $moduleData['slug'],
                'description' => $moduleData['description'],
                'is_active' => $moduleData['is_active']
            ]);

            $this->command->info("    ✓ Module: {$module->name}");
            
            // Create permissions for this module
            foreach ($moduleData['permissions'] as $permissionName) {
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'admin'
                ]);
            }
        }
    }

    private function createGranularPermissions(): void
    {
        // System-level permissions
        $systemPermissions = [
            'system.manage' => 'Full system administration',
            'organizations.manage' => 'Manage organizations',
            'organizations.create' => 'Create organizations',
            'organizations.view' => 'View organizations',
            'organizations.edit' => 'Edit organizations',
            'organizations.delete' => 'Delete organizations',
            'organizations.activate' => 'Activate/Deactivate organizations',
            'organizations.view_activation_key' => 'View organization activation keys',
            'organizations.regenerate_key' => 'Regenerate organization activation keys',
            'subscriptions.manage' => 'Manage subscription plans',
            'system.reports' => 'View system-wide reports'
        ];

        foreach ($systemPermissions as $permission => $description) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        $this->command->info('    ✓ Created granular permissions for all modules');
    }

    private function createTieredSubscriptionPlans(): void
    {
        $plans = [
            // Basic Plan
            [
                'name' => 'Basic',
                'modules' => [
                    ['name' => 'order', 'tier' => 'basic'],
                    ['name' => 'menu', 'tier' => 'basic'],
                    ['name' => 'customer', 'tier' => 'basic']
                ],
                'features' => ['basic_pos', 'simple_menu', 'customer_database'],
                'price' => 2500,
                'currency' => 'LKR',
                'description' => 'Essential features for small restaurants',
                'is_trial' => false,
                'trial_period_days' => 14,
                'max_branches' => 1,
                'max_employees' => 5,
                'is_active' => true
            ],
            
            // Premium Plan
            [
                'name' => 'Premium',
                'modules' => [
                    ['name' => 'order', 'tier' => 'premium'],
                    ['name' => 'reservation', 'tier' => 'premium'],
                    ['name' => 'inventory', 'tier' => 'premium'],
                    ['name' => 'menu', 'tier' => 'premium'],
                    ['name' => 'customer', 'tier' => 'premium'],
                    ['name' => 'report', 'tier' => 'premium']
                ],
                'features' => [
                    'advanced_pos', 'reservation_system', 'inventory_management',
                    'detailed_reporting', 'customer_loyalty', 'multi_branch_support'
                ],
                'price' => 7500,
                'currency' => 'LKR',
                'description' => 'Complete restaurant management solution',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 5,
                'max_employees' => 25,
                'is_active' => true
            ],

            // Enterprise Plan
            [
                'name' => 'Enterprise',
                'modules' => [
                    ['name' => 'order', 'tier' => 'enterprise'],
                    ['name' => 'reservation', 'tier' => 'enterprise'],
                    ['name' => 'inventory', 'tier' => 'enterprise'],
                    ['name' => 'menu', 'tier' => 'enterprise'],
                    ['name' => 'customer', 'tier' => 'enterprise'],
                    ['name' => 'report', 'tier' => 'enterprise'],
                    ['name' => 'supplier', 'tier' => 'enterprise'],
                    ['name' => 'production', 'tier' => 'enterprise']
                ],
                'features' => [
                    'all_features', 'unlimited_branches', 'advanced_analytics',
                    'api_integration', 'white_labeling', 'priority_support',
                    'custom_integrations', 'advanced_reporting'
                ],
                'price' => 25000,
                'currency' => 'LKR',
                'description' => 'Full-featured solution for large restaurant chains',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => null, // Unlimited
                'max_employees' => null, // Unlimited
                'is_active' => true
            ],

            // Trial Plan
            [
                'name' => 'Free Trial',
                'modules' => [
                    ['name' => 'order', 'tier' => 'basic'],
                    ['name' => 'menu', 'tier' => 'basic']
                ],
                'features' => ['limited_pos', 'basic_menu'],
                'price' => 0,
                'currency' => 'LKR',
                'description' => '14-day free trial with basic features',
                'is_trial' => true,
                'trial_period_days' => 14,
                'max_branches' => 1,
                'max_employees' => 3,
                'is_active' => true
            ]
        ];

        foreach ($plans as $planData) {
            $plan = SubscriptionPlan::create($planData);
            $this->command->info("    ✓ Subscription Plan: {$plan->name} ({$plan->currency} {$plan->price})");
        }
    }

    private function createSystemRoles(): void
    {
        $roles = [
            [
                'name' => 'Super Administrator',
                'guard_name' => 'admin',
                'permissions' => ['system.manage', 'organizations.manage', 'subscriptions.manage']
            ],
            [
                'name' => 'Organization Administrator',
                'guard_name' => 'admin',
                'permissions' => ['order.manage', 'reservation.manage', 'inventory.manage', 'menu.manage', 'report.view']
            ],
            [
                'name' => 'Branch Administrator',
                'guard_name' => 'admin',
                'permissions' => ['order.view', 'order.create', 'reservation.view', 'menu.view', 'inventory.view']
            ],
            [
                'name' => 'Staff Member',
                'guard_name' => 'admin',
                'permissions' => ['order.create', 'order.view', 'menu.view']
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::create([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name']
            ]);
            
            // Assign permissions
            $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->syncPermissions($permissions);
            
            $this->command->info("    ✓ Role: {$role->name} with " . count($permissions) . " permissions");
        }
    }
}
