<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds following UI/UX guidelines.
     */
    public function run(): void
    {
        $this->command->info('📦 Seeding system modules with comprehensive permissions...');

        $modules = [
            // Core Dashboard Module
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'permissions' => [
                    'dashboard.view',
                    'dashboard.stats',
                    'dashboard.widgets',
                    'dashboard.analytics',
                ],
                'description' => 'System dashboard overview with real-time analytics',
                'is_active' => true,
            ],
            
            // Inventory Management Module
            [
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'permissions' => [
                    'inventory.view',
                    'inventory.manage',
                    'inventory.create',
                    'inventory.edit',
                    'inventory.delete',
                    'inventory.export',
                    'inventory.import',
                    'inventory.alerts',
                    'inventory.audit',
                ],
                'description' => 'Complete inventory management system',
                'is_active' => true,
            ],
            
            // Reservation Management Module
            [
                'name' => 'Reservation Management',
                'slug' => 'reservations',
                'permissions' => [
                    'reservations.view',
                    'reservations.manage',
                    'reservations.create',
                    'reservations.edit',
                    'reservations.delete',
                    'reservations.export',
                    'reservations.confirm',
                    'reservations.cancel',
                ],
                'description' => 'Customer reservation management system',
                'is_active' => true,
            ],
            
            // Order Management Module
            [
                'name' => 'Order Management',
                'slug' => 'orders',
                'permissions' => [
                    'orders.view',
                    'orders.manage',
                    'orders.create',
                    'orders.edit',
                    'orders.delete',
                    'orders.refund',
                    'orders.export',
                    'orders.kitchen',
                    'orders.payment',
                ],
                'description' => 'Complete order processing and management',
                'is_active' => true,
            ],
            
            // Kitchen Operations Module
            [
                'name' => 'Kitchen Operations',
                'slug' => 'kitchen',
                'permissions' => [
                    'kitchen.view',
                    'kitchen.manage',
                    'kitchen.kot.view',
                    'kitchen.kot.update',
                    'kitchen.stations.manage',
                    'kitchen.workflow',
                ],
                'description' => 'Kitchen workflow and KOT management',
                'is_active' => true,
            ],
            
            // Reports Module
            [
                'name' => 'Reports & Analytics',
                'slug' => 'reports',
                'permissions' => [
                    'reports.view',
                    'reports.generate',
                    'reports.export',
                    'reports.delete',
                    'reports.sales',
                    'reports.inventory',
                    'reports.staff',
                ],
                'description' => 'Comprehensive reporting and analytics',
                'is_active' => true,
            ],
            
            // Customer Management Module
            [
                'name' => 'Customer Management',
                'slug' => 'customers',
                'permissions' => [
                    'customers.view',
                    'customers.manage',
                    'customers.create',
                    'customers.edit',
                    'customers.delete',
                    'customers.export',
                    'customers.loyalty',
                ],
                'description' => 'Customer relationship management',
                'is_active' => true,
            ],
            
            // Staff Management Module
            [
                'name' => 'Staff Management',
                'slug' => 'staff',
                'permissions' => [
                    'staff.view',
                    'staff.manage',
                    'staff.create',
                    'staff.edit',
                    'staff.delete',
                    'staff.scheduling',
                    'staff.payroll',
                ],
                'description' => 'Employee and staff management system',
                'is_active' => true,
            ],
            
            // Suppliers Module
            [
                'name' => 'Supplier Management',
                'slug' => 'suppliers',
                'permissions' => [
                    'suppliers.view',
                    'suppliers.manage',
                    'suppliers.create',
                    'suppliers.edit',
                    'suppliers.delete',
                    'suppliers.export',
                    'suppliers.orders',
                ],
                'description' => 'Supplier and vendor management',
                'is_active' => true,
            ],
            
            // Menu Management Module
            [
                'name' => 'Menu Management',
                'slug' => 'menu',
                'permissions' => [
                    'menu.view',
                    'menu.manage',
                    'menu.create',
                    'menu.edit',
                    'menu.delete',
                    'menu.categories',
                    'menu.pricing',
                    'menu.promotions',
                ],
                'description' => 'Restaurant menu and item management',
                'is_active' => true,
            ],
            
            // User Management Module
            [
                'name' => 'User Management',
                'slug' => 'users',
                'permissions' => [
                    'users.view',
                    'users.manage',
                    'users.create',
                    'users.edit',
                    'users.delete',
                    'users.activate',
                    'users.deactivate',
                    'users.permissions',
                ],
                'description' => 'System user management',
                'is_active' => true,
            ],
            
            // Organization Management Module
            [
                'name' => 'Organization Management',
                'slug' => 'organizations',
                'permissions' => [
                    'organizations.view',
                    'organizations.manage',
                    'organizations.create',
                    'organizations.edit',
                    'organizations.delete',
                    'organizations.activate',
                    'organizations.deactivate',
                    'organizations.subscriptions',
                ],
                'description' => 'Multi-organization management',
                'is_active' => true,
            ],
            
            // Branch Management Module
            [
                'name' => 'Branch Management',
                'slug' => 'branches',
                'permissions' => [
                    'branches.view',
                    'branches.manage',
                    'branches.create',
                    'branches.edit',
                    'branches.delete',
                    'branches.activate',
                    'branches.deactivate',
                    'branches.settings',
                ],
                'description' => 'Multi-branch operations management',
                'is_active' => true,
            ],
            
            // Subscription Management Module
            [
                'name' => 'Subscription Management',
                'slug' => 'subscriptions',
                'permissions' => [
                    'subscriptions.view',
                    'subscriptions.manage',
                    'subscriptions.create',
                    'subscriptions.edit',
                    'subscriptions.delete',
                    'subscriptions.activate',
                    'subscriptions.deactivate',
                    'subscriptions.billing',
                ],
                'description' => 'Subscription plan management',
                'is_active' => true,
            ],
            
            // Financial Management Module
            [
                'name' => 'Financial Management',
                'slug' => 'finance',
                'permissions' => [
                    'finance.view',
                    'finance.manage',
                    'finance.payments',
                    'finance.invoices',
                    'finance.expenses',
                    'finance.reports',
                    'finance.taxes',
                ],
                'description' => 'Financial operations and accounting',
                'is_active' => true,
            ],
            
            // Roles & Permissions Module
            [
                'name' => 'Roles & Permissions',
                'slug' => 'roles',
                'permissions' => [
                    'roles.view',
                    'roles.manage',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                    'roles.assign',
                    'permissions.manage',
                ],
                'description' => 'Role-based access control system',
                'is_active' => true,
            ],
            
            // Table Management Module
            [
                'name' => 'Table Management',
                'slug' => 'tables',
                'permissions' => [
                    'tables.view',
                    'tables.manage',
                    'tables.create',
                    'tables.edit',
                    'tables.delete',
                    'tables.layout',
                    'tables.status',
                ],
                'description' => 'Restaurant table and seating management',
                'is_active' => true,
            ],
            
            // POS System Module
            [
                'name' => 'Point of Sale',
                'slug' => 'pos',
                'permissions' => [
                    'pos.view',
                    'pos.operate',
                    'pos.transactions',
                    'pos.refunds',
                    'pos.reports',
                    'pos.settings',
                ],
                'description' => 'Point of sale system operations',
                'is_active' => true,
            ],
            
            // System Settings Module
            [
                'name' => 'System Settings',
                'slug' => 'settings',
                'permissions' => [
                    'settings.view',
                    'settings.manage',
                    'settings.update',
                    'settings.backup',
                    'settings.restore',
                    'settings.maintenance',
                ],
                'description' => 'Global system configuration and settings',
                'is_active' => true,
            ],
            
            // Modules Management Module
            [
                'name' => 'Module Management',
                'slug' => 'modules',
                'permissions' => [
                    'modules.view',
                    'modules.manage',
                    'modules.create',
                    'modules.edit',
                    'modules.delete',
                    'modules.activate',
                    'modules.deactivate',
                ],
                'description' => 'System module configuration',
                'is_active' => true,
            ],
        ];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($modules as $moduleData) {
            $module = Module::updateOrCreate(
                ['slug' => $moduleData['slug']], // Find by slug (unique identifier)
                $moduleData // Update or create with this data
            );
            
            if ($module->wasRecentlyCreated) {
                $createdCount++;
                $this->command->line("  ✅ Created module: {$moduleData['name']}");
            } else {
                $updatedCount++;
                $this->command->line("  🔄 Updated module: {$moduleData['name']}");
            }
        }

        $this->command->info("  📦 Modules seeding completed:");
        $this->command->info("    • {$createdCount} modules created");
        $this->command->info("    • {$updatedCount} modules updated");
        $this->command->info("    • Total modules: " . Module::count());
        
        // Display module status summary following UI/UX guidelines
        $this->displayModuleSummary();
    }

    /**
     * Display module summary following UI/UX guidelines
     */
    private function displayModuleSummary(): void
    {
        $activeModules = Module::where('is_active', true)->count();
        $inactiveModules = Module::where('is_active', false)->count();
        
        $this->command->newLine();
        $this->command->info('📊 Module Status Summary:');
        $this->command->line("  🟢 Active Modules: {$activeModules}");
        $this->command->line("  🔴 Inactive Modules: {$inactiveModules}");
        
        // Display core modules for verification
        $coreModules = ['dashboard', 'inventory', 'orders', 'kitchen', 'reservations'];
        $this->command->newLine();
        $this->command->info('🔧 Core Modules Status:');
        
        foreach ($coreModules as $coreSlug) {
            $module = Module::where('slug', $coreSlug)->first();
            if ($module) {
                $status = $module->is_active ? '✅ Active' : '❌ Inactive';
                $this->command->line("  • {$module->name}: {$status}");
            } else {
                $this->command->line("  • {$coreSlug}: ❌ Missing");
            }
        }
    }
}
