<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'permissions' => [
                    'dashboard.view',
                    'dashboard.stats',
                    'dashboard.widgets',
                ],
                'description' => 'System dashboard overview'
            ],
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
                ],
                'description' => 'Manage restaurant inventory'
            ],
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
                ],
                'description' => 'Manage customer reservations'
            ],
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
                ],
                'description' => 'Manage customer orders'
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'permissions' => [
                    'reports.view',
                    'reports.generate',
                    'reports.export',
                    'reports.delete',
                ],
                'description' => 'Access system reports'
            ],
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
                ],
                'description' => 'Manage customer information'
            ],
            [
                'name' => 'Suppliers',
                'slug' => 'suppliers',
                'permissions' => [
                    'suppliers.view',
                    'suppliers.manage',
                    'suppliers.create',
                    'suppliers.edit',
                    'suppliers.delete',
                    'suppliers.export',
                ],
                'description' => 'Manage suppliers and vendors'
            ],
            [
                'name' => 'Users',
                'slug' => 'users',
                'permissions' => [
                    'users.view',
                    'users.manage',
                    'users.create',
                    'users.edit',
                    'users.delete',
                    'users.activate',
                    'users.deactivate',
                ],
                'description' => 'Manage system users'
            ],
            [
                'name' => 'Organizations',
                'slug' => 'organizations',
                'permissions' => [
                    'organizations.view',
                    'organizations.manage',
                    'organizations.create',
                    'organizations.edit',
                    'organizations.delete',
                    'organizations.activate',
                    'organizations.deactivate',
                ],
                'description' => 'Manage organizations'
            ],
            [
                'name' => 'Activate Organization',
                'slug' => 'activate_organization',
                'permissions' => [
                    'organizations.activate',
                    'organizations.deactivate',
                ],
                'description' => 'Activate/deactivate organizations'
            ],
            [
                'name' => 'Branches',
                'slug' => 'branches',
                'permissions' => [
                    'branches.view',
                    'branches.manage',
                    'branches.create',
                    'branches.edit',
                    'branches.delete',
                    'branches.activate',
                    'branches.deactivate',
                ],
                'description' => 'Manage branches'
            ],
            [
                'name' => 'Activate Branch',
                'slug' => 'activate_branch',
                'permissions' => [
                    'branches.activate',
                    'branches.deactivate',
                ],
                'description' => 'Activate/deactivate branches'
            ],
            [
                'name' => 'Subscription Plans',
                'slug' => 'subscriptions',
                'permissions' => [
                    'subscriptions.view',
                    'subscriptions.manage',
                    'subscriptions.create',
                    'subscriptions.edit',
                    'subscriptions.delete',
                    'subscriptions.activate',
                    'subscriptions.deactivate',
                ],
                'description' => 'Manage subscription plans'
            ],
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
                ],
                'description' => 'Manage roles and permissions'
            ],
            [
                'name' => 'Modules Management',
                'slug' => 'modules',
                'permissions' => [
                    'modules.view',
                    'modules.manage',
                    'modules.create',
                    'modules.edit',
                    'modules.delete',
                ],
                'description' => 'Manage system modules'
            ],
            [
                'name' => 'System Settings',
                'slug' => 'settings',
                'permissions' => [
                    'settings.view',
                    'settings.manage',
                    'settings.update',
                ],
                'description' => 'Configure system settings'
            ],
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
