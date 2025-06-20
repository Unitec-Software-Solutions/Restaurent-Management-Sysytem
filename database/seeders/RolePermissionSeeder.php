<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Organization;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Restaurant employee roles with their specific permissions
        $restaurantRoles = [
            'host/hostess' => [
                'manage-reservations',
                'view-restaurant-layout',
                'view-table-status',
                'customer-service',
                'view-waitlist'
            ],
            'servers' => [
                'take-orders',
                'modify-orders',
                'view-menu',
                'process-payments',
                'customer-service',
                'view-table-assignments'
            ],
            'bartenders' => [
                'manage-bar-inventory',
                'prepare-beverages',
                'view-bar-orders',
                'cash-handling',
                'view-menu'
            ],
            'cashiers' => [
                'process-payments',
                'handle-refunds',
                'cash-handling',
                'print-receipts',
                'view-sales-reports'
            ],
            'chefs' => [
                'view-kitchen-orders',
                'update-order-status',
                'manage-kitchen-inventory',
                'view-recipes',
                'kitchen-operations'
            ],
            'dishwashers' => [
                'kitchen-support',
                'equipment-maintenance',
                'view-cleaning-schedule'
            ],
            'kitchen-managers' => [
                'manage-kitchen-staff',
                'kitchen-operations',
                'manage-kitchen-inventory',
                'view-kitchen-reports',
                'approve-menu-changes',
                'schedule-management'
            ]
        ];

        // Create all restaurant permissions first
        $allRestaurantPermissions = [];
        foreach ($restaurantRoles as $permissions) {
            $allRestaurantPermissions = array_merge($allRestaurantPermissions, $permissions);
        }
        $allRestaurantPermissions = array_unique($allRestaurantPermissions);

        foreach ($allRestaurantPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create restaurant roles and assign permissions
        foreach ($restaurantRoles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }        // Get all permissions for admin roles (only existing ones)
        $allPermissions = Permission::where('guard_name', 'admin')->pluck('name')->toArray();

        // Super Admin: all admin permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'admin',
            'organization_id' => null,
        ]);
        if (!empty($allPermissions)) {
            $superAdminRole->syncPermissions($allPermissions);
        }

        // Organization Admin: all permissions except inventory (if they exist)
        $orgAdminPerms = Permission::where('guard_name', 'admin')
            ->whereNotIn('name', [
                'manage_inventory', 'adjust_inventory', 'audit_inventory', 'supplier_inventory'
            ])->pluck('name')->toArray();

        foreach (Organization::all() as $org) {            $orgAdminRole = Role::firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'admin',
                'organization_id' => $org->id,
            ]);
            if (!empty($orgAdminPerms)) {
                $orgAdminRole->syncPermissions($orgAdminPerms);
            }

            foreach ($org->branches as $branch) {
                // Branch Manager: reservation and order permissions only (if they exist)
                $branchManagerPerms = Permission::where('guard_name', 'admin')
                    ->whereIn('name', [
                        'create_reservation', 'view_reservation', 'edit_reservation', 'delete_reservation', 'manage_reservation',
                        'create_order', 'process_order', 'cancel_order', 'refund_order', 'report_order',
                    ])->pluck('name')->toArray();

                $branchManagerRole = Role::firstOrCreate([
                    'name' => 'Branch Manager',
                    'guard_name' => 'admin',
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                ]);
                if (!empty($branchManagerPerms)) {
                    $branchManagerRole->syncPermissions($branchManagerPerms);
                }

                // Inventory Manager: inventory only (if permissions exist)
                $inventoryPerms = Permission::where('guard_name', 'admin')
                    ->whereIn('name', [
                        'manage_inventory', 'adjust_inventory', 'audit_inventory', 'supplier_inventory',
                    ])->pluck('name')->toArray();

                $inventoryRole = Role::firstOrCreate([
                    'name' => 'Inventory Manager',
                    'guard_name' => 'admin',
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                ]);
                if (!empty($inventoryPerms)) {
                    $inventoryRole->syncPermissions($inventoryPerms);
                }

                // Cashier: reservation and order permissions only (if they exist)
                $cashierPerms = Permission::where('guard_name', 'admin')
                    ->whereIn('name', [
                        'create_reservation', 'view_reservation', 'edit_reservation', 'delete_reservation', 'manage_reservation',
                        'create_order', 'process_order', 'cancel_order', 'refund_order', 'report_order',
                    ])->pluck('name')->toArray();

                $cashierRole = Role::firstOrCreate([
                    'name' => 'Cashier',
                    'guard_name' => 'admin',
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                ]);
                if (!empty($cashierPerms)) {
                    $cashierRole->syncPermissions($cashierPerms);
                }
            }
        }
    }
}
