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
        // Get all permissions
        $allPermissions = Permission::pluck('name')->toArray();

        // Super Admin: all permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'admin',
            'organization_id' => null,
        ]);
        $superAdminRole->syncPermissions($allPermissions);

        // Organization Admin: all permissions except inventory
        $orgAdminPerms = Permission::whereNotIn('name', [
            'manage_inventory', 'adjust_inventory', 'audit_inventory', 'supplier_inventory'
        ])->pluck('name')->toArray();

        foreach (Organization::all() as $org) {
            $orgAdminRole = Role::firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'admin',
                'organization_id' => $org->id,
            ]);
            $orgAdminRole->syncPermissions($orgAdminPerms);

            foreach ($org->branches as $branch) {
                // Branch Manager: reservation and order permissions only
                $branchManagerPerms = Permission::whereIn('name', [
                    'create_reservation', 'view_reservation', 'edit_reservation', 'delete_reservation', 'manage_reservation',
                    'create_order', 'process_order', 'cancel_order', 'refund_order', 'report_order',
                ])->pluck('name')->toArray();

                $branchManagerRole = Role::firstOrCreate([
                    'name' => 'Branch Manager',
                    'guard_name' => 'admin',
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                ]);
                $branchManagerRole->syncPermissions($branchManagerPerms);

                // Inventory Manager: inventory only
                $inventoryPerms = Permission::whereIn('name', [
                    'manage_inventory', 'adjust_inventory', 'audit_inventory', 'supplier_inventory',
                ])->pluck('name')->toArray();

                $inventoryRole = Role::firstOrCreate([
                    'name' => 'Inventory Manager',
                    'guard_name' => 'admin',
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                ]);
                $inventoryRole->syncPermissions($inventoryPerms);

                // Cashier: reservation and order permissions only
                $cashierPerms = Permission::whereIn('name', [
                    'create_reservation', 'view_reservation', 'edit_reservation', 'delete_reservation', 'manage_reservation',
                    'create_order', 'process_order', 'cancel_order', 'refund_order', 'report_order',
                ])->pluck('name')->toArray();

                $cashierRole = Role::firstOrCreate([
                    'name' => 'Cashier',
                    'guard_name' => 'admin',
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                ]);
                $cashierRole->syncPermissions($cashierPerms);
            }
        }
    }
}
