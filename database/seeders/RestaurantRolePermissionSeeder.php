<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Employee;

class RestaurantRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define restaurant roles and their permissions
        $restaurantRoles = [
            'host/hostess' => [
                'permissions' => [
                    'manage-reservations',
                    'view-restaurant-layout',
                    'view-table-status',
                    'customer-service',
                    'view-waitlist'
                ],
                'description' => 'Manages guest seating, reservations, and front-of-house customer service'
            ],
            'servers' => [
                'permissions' => [
                    'take-orders',
                    'modify-orders',
                    'view-menu',
                    'process-payments',
                    'customer-service',
                    'view-table-assignments'
                ],
                'description' => 'Takes customer orders, serves food and beverages, handles table service'
            ],
            'bartenders' => [
                'permissions' => [
                    'manage-bar-inventory',
                    'prepare-beverages',
                    'view-bar-orders',
                    'cash-handling',
                    'view-menu'
                ],
                'description' => 'Prepares alcoholic and non-alcoholic beverages, manages bar operations'
            ],
            'cashiers' => [
                'permissions' => [
                    'process-payments',
                    'handle-refunds',
                    'cash-handling',
                    'print-receipts',
                    'view-sales-reports'
                ],
                'description' => 'Processes customer payments, handles cash transactions and receipts'
            ],
            'chefs' => [
                'permissions' => [
                    'view-kitchen-orders',
                    'update-order-status',
                    'manage-kitchen-inventory',
                    'view-recipes',
                    'kitchen-operations'
                ],
                'description' => 'Prepares food, manages kitchen operations, maintains food quality standards'
            ],
            'dishwashers' => [
                'permissions' => [
                    'kitchen-support',
                    'equipment-maintenance',
                    'view-cleaning-schedule'
                ],
                'description' => 'Maintains kitchen cleanliness, washes dishes, supports kitchen operations'
            ],
            'kitchen-managers' => [
                'permissions' => [
                    'manage-kitchen-staff',
                    'kitchen-operations',
                    'manage-kitchen-inventory',
                    'view-kitchen-reports',
                    'approve-menu-changes',
                    'schedule-management'
                ],
                'description' => 'Oversees kitchen staff, manages inventory, ensures kitchen efficiency'
            ]
        ];

        // Create all unique permissions first
        $allPermissions = [];
        foreach ($restaurantRoles as $roleData) {
            $allPermissions = array_merge($allPermissions, $roleData['permissions']);
        }
        $allPermissions = array_unique($allPermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions
        foreach ($restaurantRoles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            // Sync permissions for this role
            $role->syncPermissions($roleData['permissions']);

            $this->command->info("Created role: {$roleName} with " . count($roleData['permissions']) . " permissions");
        }

        $this->command->info('Restaurant roles and permissions seeded successfully!');
    }

    /**
     * Get the role-permission mapping for documentation
     */
    public static function getRolePermissionMapping(): array
    {
        return [
            'host/hostess' => [
                'permissions' => ['manage-reservations', 'view-restaurant-layout', 'view-table-status', 'customer-service', 'view-waitlist'],
                'middleware' => ['role:host/hostess', 'permission:manage-reservations'],
                'blade_checks' => ['@can(\'manage-reservations\')', '@hasrole(\'host/hostess\')']
            ],
            'servers' => [
                'permissions' => ['take-orders', 'modify-orders', 'view-menu', 'process-payments', 'customer-service', 'view-table-assignments'],
                'middleware' => ['role:servers', 'permission:take-orders'],
                'blade_checks' => ['@can(\'take-orders\')', '@hasrole(\'servers\')']
            ],
            'bartenders' => [
                'permissions' => ['manage-bar-inventory', 'prepare-beverages', 'view-bar-orders', 'cash-handling', 'view-menu'],
                'middleware' => ['role:bartenders', 'permission:manage-bar-inventory'],
                'blade_checks' => ['@can(\'manage-bar-inventory\')', '@hasrole(\'bartenders\')']
            ],
            'cashiers' => [
                'permissions' => ['process-payments', 'handle-refunds', 'cash-handling', 'print-receipts', 'view-sales-reports'],
                'middleware' => ['role:cashiers', 'permission:process-payments'],
                'blade_checks' => ['@can(\'process-payments\')', '@hasrole(\'cashiers\')']
            ],
            'chefs' => [
                'permissions' => ['view-kitchen-orders', 'update-order-status', 'manage-kitchen-inventory', 'view-recipes', 'kitchen-operations'],
                'middleware' => ['role:chefs', 'permission:view-kitchen-orders'],
                'blade_checks' => ['@can(\'view-kitchen-orders\')', '@hasrole(\'chefs\')']
            ],
            'dishwashers' => [
                'permissions' => ['kitchen-support', 'equipment-maintenance', 'view-cleaning-schedule'],
                'middleware' => ['role:dishwashers', 'permission:kitchen-support'],
                'blade_checks' => ['@can(\'kitchen-support\')', '@hasrole(\'dishwashers\')']
            ],
            'kitchen-managers' => [
                'permissions' => ['manage-kitchen-staff', 'kitchen-operations', 'manage-kitchen-inventory', 'view-kitchen-reports', 'approve-menu-changes', 'schedule-management'],
                'middleware' => ['role:kitchen-managers', 'permission:manage-kitchen-staff'],
                'blade_checks' => ['@can(\'manage-kitchen-staff\')', '@hasrole(\'kitchen-managers\')']
            ]
        ];
    }
}
