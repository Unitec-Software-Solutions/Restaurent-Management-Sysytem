<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create the restaurant-specific roles
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

        // Create all permissions first
        $allPermissions = [];
        foreach ($restaurantRoles as $permissions) {
            $allPermissions = array_merge($allPermissions, $permissions);
        }
        $allPermissions = array_unique($allPermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        foreach ($restaurantRoles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        // Add role_id column to employees table if it doesn't exist
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'role_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable()->after('id');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            });
        }

        // Update existing employees to have default roles based on their current role field
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'role')) {
            $employees = \App\Models\Employee::all();
            foreach ($employees as $employee) {
                $roleName = $this->mapOldRoleToNew($employee->role);
                if ($roleName) {
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $employee->role_id = $role->id;
                        $employee->save();
                        // Also assign the role using Spatie's package
                        $employee->assignRole($role);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove role assignments from employees
        if (Schema::hasTable('employees')) {
            \App\Models\Employee::query()->update(['role_id' => null]);
        }

        // Remove the roles and permissions
        $roleNames = ['host/hostess', 'servers', 'bartenders', 'cashiers', 'chefs', 'dishwashers', 'kitchen-managers'];
        
        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->delete();
            }
        }

        // Remove permissions that are specific to restaurant operations
        $permissions = [
            'manage-reservations', 'view-restaurant-layout', 'view-table-status',
            'take-orders', 'modify-orders', 'view-menu', 'process-payments',
            'manage-bar-inventory', 'prepare-beverages', 'view-bar-orders',
            'handle-refunds', 'cash-handling', 'print-receipts',
            'view-kitchen-orders', 'update-order-status', 'manage-kitchen-inventory',
            'kitchen-support', 'equipment-maintenance', 'view-cleaning-schedule',
            'manage-kitchen-staff', 'kitchen-operations', 'view-kitchen-reports',
            'approve-menu-changes', 'schedule-management', 'customer-service',
            'view-waitlist', 'view-table-assignments', 'view-recipes',
            'view-sales-reports'
        ];

        foreach ($permissions as $permission) {
            Permission::where('name', $permission)->delete();
        }

        // Drop role_id column from employees table
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'role_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }
    }

    /**
     * Map old role names to new restaurant roles
     */
    private function mapOldRoleToNew($oldRole): ?string
    {
        $mapping = [
            'waiter' => 'servers',
            'waitress' => 'servers',
            'server' => 'servers',
            'steward' => 'servers',
            'chef' => 'chefs',
            'cook' => 'chefs',
            'bartender' => 'bartenders',
            'cashier' => 'cashiers',
            'host' => 'host/hostess',
            'hostess' => 'host/hostess',
            'dishwasher' => 'dishwashers',
            'kitchen_manager' => 'kitchen-managers',
            'manager' => 'kitchen-managers'
        ];

        return $mapping[strtolower($oldRole)] ?? 'servers'; // Default to servers
    }
};
