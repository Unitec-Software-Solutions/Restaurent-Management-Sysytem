<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\CustomRole;

class EnhancedPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define comprehensive admin permissions
        $adminPermissions = [
            // Organization Management
            'manage_organizations',
            'create_organizations',
            'edit_organizations',
            'delete_organizations',
            'view_organizations',

            // Branch Management
            'manage_branches',
            'create_branches',
            'edit_branches',
            'delete_branches',
            'view_branches',
            'activate_branches',
            'deactivate_branches',

            // User Management
            'manage_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_users',
            'assign_roles',

            // Role and Permission Management
            'manage_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_roles',
            'manage_permissions',

            // Inventory Management
            'manage_inventory',
            'create_inventory_items',
            'edit_inventory_items',
            'delete_inventory_items',
            'view_inventory',
            'adjust_inventory',
            'transfer_inventory',
            'audit_inventory',

            // Order Management
            'manage_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            'view_orders',
            'process_orders',
            'cancel_orders',
            'refund_orders',

            // Reservation Management
            'manage_reservations',
            'create_reservations',
            'edit_reservations',
            'delete_reservations',
            'view_reservations',
            'approve_reservations',
            'cancel_reservations',

            // Menu Management
            'manage_menu',
            'create_menu_items',
            'edit_menu_items',
            'delete_menu_items',
            'view_menu',
            'manage_menu_categories',

            // Kitchen Management
            'manage_kitchen',
            'view_kitchen_orders',
            'update_order_status',
            'manage_kitchen_stations',

            // Reports and Analytics
            'view_reports',
            'generate_reports',
            'export_reports',
            'view_analytics',
            'view_financial_reports',

            // System Configuration
            'manage_settings',
            'view_system_logs',
            'manage_modules',
            'configure_notifications',

            // Staff Management
            'manage_staff',
            'create_staff',
            'edit_staff',
            'delete_staff',
            'view_staff',
            'manage_schedules',

            // Financial Management
            'manage_payments',
            'process_refunds',
            'view_financial_data',
            'manage_pricing',

            // Subscription Management (Org Admin only)
            'manage_subscriptions',
            'view_billing',
            'upgrade_plans',
        ];

        // Create all permissions
        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        $this->command->info('Admin permissions created successfully!');

        // Create default role templates (these will be created per organization)
        $this->createDefaultRoleTemplates();

        $this->command->info('Enhanced permission system seeded successfully!');
    }

    private function createDefaultRoleTemplates(): void
    {
        // Define role templates with their permissions
        $roleTemplates = [
            'super_admin' => [
                'description' => 'System super administrator with full access',
                'permissions' => 'all',
                'scope' => 'global',
            ],
            'org_admin' => [
                'description' => 'Organization administrator with full org access',
                'permissions' => [
                    'manage_branches', 'create_branches', 'edit_branches', 'delete_branches', 'view_branches',
                    'activate_branches', 'deactivate_branches',
                    'manage_users', 'create_users', 'edit_users', 'delete_users', 'view_users', 'assign_roles',
                    'manage_roles', 'create_roles', 'edit_roles', 'delete_roles', 'view_roles',
                    'manage_inventory', 'view_inventory', 'audit_inventory',
                    'view_reports', 'generate_reports', 'export_reports', 'view_analytics', 'view_financial_reports',
                    'manage_settings', 'manage_modules',
                    'manage_staff', 'view_staff',
                    'manage_subscriptions', 'view_billing', 'upgrade_plans',
                ],
                'scope' => 'organization',
            ],
            'branch_admin' => [
                'description' => 'Branch administrator with branch-level access',
                'permissions' => [
                    'view_branches',
                    'manage_users', 'create_users', 'edit_users', 'view_users',
                    'manage_inventory', 'create_inventory_items', 'edit_inventory_items', 'view_inventory',
                    'adjust_inventory', 'transfer_inventory',
                    'manage_orders', 'create_orders', 'edit_orders', 'view_orders', 'process_orders', 'cancel_orders',
                    'manage_reservations', 'create_reservations', 'edit_reservations', 'view_reservations',
                    'approve_reservations', 'cancel_reservations',
                    'manage_menu', 'create_menu_items', 'edit_menu_items', 'view_menu',
                    'manage_kitchen', 'view_kitchen_orders', 'update_order_status', 'manage_kitchen_stations',
                    'view_reports', 'generate_reports', 'view_analytics',
                    'manage_staff', 'create_staff', 'edit_staff', 'view_staff', 'manage_schedules',
                    'manage_payments', 'process_refunds',
                ],
                'scope' => 'branch',
            ],
            'branch_manager' => [
                'description' => 'Branch manager with operational access',
                'permissions' => [
                    'view_branches',
                    'view_users',
                    'manage_orders', 'create_orders', 'edit_orders', 'view_orders', 'process_orders',
                    'manage_reservations', 'create_reservations', 'edit_reservations', 'view_reservations',
                    'approve_reservations',
                    'view_menu',
                    'view_kitchen_orders', 'update_order_status',
                    'view_reports', 'view_analytics',
                    'view_staff', 'manage_schedules',
                    'manage_payments',
                ],
                'scope' => 'branch',
            ],
            'inventory_manager' => [
                'description' => 'Inventory specialist with stock management access',
                'permissions' => [
                    'manage_inventory', 'create_inventory_items', 'edit_inventory_items', 'view_inventory',
                    'adjust_inventory', 'transfer_inventory', 'audit_inventory',
                    'view_reports',
                ],
                'scope' => 'branch',
            ],
            'cashier' => [
                'description' => 'Cashier with payment and order processing access',
                'permissions' => [
                    'create_orders', 'view_orders', 'process_orders',
                    'manage_payments', 'process_refunds',
                    'create_reservations', 'view_reservations',
                ],
                'scope' => 'branch',
            ],
        ];

        // Store role templates for later use by observers
        cache()->put('role_templates', $roleTemplates, now()->addDays(30));

        $this->command->info('Role templates cached for automated creation');
    }

    /**
     * Get permissions for a specific role template
     */
    public static function getPermissionsForRole(string $roleName): array
    {
        $templates = cache()->get('role_templates', []);

        if (!isset($templates[$roleName])) {
            return [];
        }

        $template = $templates[$roleName];

        if ($template['permissions'] === 'all') {
            return Permission::where('guard_name', 'admin')->pluck('name')->toArray();
        }

        return $template['permissions'];
    }
}
