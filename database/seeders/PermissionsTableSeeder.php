<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'organizations.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'organizations.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'organizations.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'organizations.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'organizations.activate',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'organizations.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'organizations.settings',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'organizations.subscription',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'branches.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'branches.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'branches.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'branches.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'branches.activate',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'branches.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'branches.settings',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'branches.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'users.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'users.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'users.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'users.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'users.activate',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'users.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'users.permissions',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'users.roles',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:23',
                'updated_at' => '2025-07-21 04:04:23',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'roles.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'roles.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'roles.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'roles.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'roles.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'roles.assign',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            30 => 
            array (
                'id' => 31,
                'name' => 'permissions.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            31 => 
            array (
                'id' => 32,
                'name' => 'permissions.assign',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            32 => 
            array (
                'id' => 33,
                'name' => 'menus.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            33 => 
            array (
                'id' => 34,
                'name' => 'menus.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            34 => 
            array (
                'id' => 35,
                'name' => 'menus.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            35 => 
            array (
                'id' => 36,
                'name' => 'menus.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            36 => 
            array (
                'id' => 37,
                'name' => 'menus.activate',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            37 => 
            array (
                'id' => 38,
                'name' => 'menus.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            38 => 
            array (
                'id' => 39,
                'name' => 'menus.categories',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            39 => 
            array (
                'id' => 40,
                'name' => 'menus.pricing',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            40 => 
            array (
                'id' => 41,
                'name' => 'orders.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            41 => 
            array (
                'id' => 42,
                'name' => 'orders.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            42 => 
            array (
                'id' => 43,
                'name' => 'orders.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            43 => 
            array (
                'id' => 44,
                'name' => 'orders.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            44 => 
            array (
                'id' => 45,
                'name' => 'orders.process',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            45 => 
            array (
                'id' => 46,
                'name' => 'orders.cancel',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            46 => 
            array (
                'id' => 47,
                'name' => 'orders.refund',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            47 => 
            array (
                'id' => 48,
                'name' => 'orders.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            48 => 
            array (
                'id' => 49,
                'name' => 'inventory.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            49 => 
            array (
                'id' => 50,
                'name' => 'inventory.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            50 => 
            array (
                'id' => 51,
                'name' => 'inventory.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            51 => 
            array (
                'id' => 52,
                'name' => 'inventory.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            52 => 
            array (
                'id' => 53,
                'name' => 'inventory.adjust',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            53 => 
            array (
                'id' => 54,
                'name' => 'inventory.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            54 => 
            array (
                'id' => 55,
                'name' => 'inventory.alerts',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            55 => 
            array (
                'id' => 56,
                'name' => 'inventory.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            56 => 
            array (
                'id' => 57,
                'name' => 'suppliers.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            57 => 
            array (
                'id' => 58,
                'name' => 'suppliers.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            58 => 
            array (
                'id' => 59,
                'name' => 'suppliers.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            59 => 
            array (
                'id' => 60,
                'name' => 'suppliers.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            60 => 
            array (
                'id' => 61,
                'name' => 'suppliers.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            61 => 
            array (
                'id' => 62,
                'name' => 'suppliers.orders',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            62 => 
            array (
                'id' => 63,
                'name' => 'suppliers.payments',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            63 => 
            array (
                'id' => 64,
                'name' => 'suppliers.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            64 => 
            array (
                'id' => 65,
                'name' => 'reservations.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            65 => 
            array (
                'id' => 66,
                'name' => 'reservations.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            66 => 
            array (
                'id' => 67,
                'name' => 'reservations.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            67 => 
            array (
                'id' => 68,
                'name' => 'reservations.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            68 => 
            array (
                'id' => 69,
                'name' => 'reservations.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            69 => 
            array (
                'id' => 70,
                'name' => 'reservations.confirm',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            70 => 
            array (
                'id' => 71,
                'name' => 'reservations.cancel',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            71 => 
            array (
                'id' => 72,
                'name' => 'reservations.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            72 => 
            array (
                'id' => 73,
                'name' => 'kitchen.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            73 => 
            array (
                'id' => 74,
                'name' => 'kitchen.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            74 => 
            array (
                'id' => 75,
                'name' => 'kitchen.orders',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            75 => 
            array (
                'id' => 76,
                'name' => 'kitchen.stations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            76 => 
            array (
                'id' => 77,
                'name' => 'kitchen.staff',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            77 => 
            array (
                'id' => 78,
                'name' => 'kitchen.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            78 => 
            array (
                'id' => 79,
                'name' => 'kitchen.settings',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            79 => 
            array (
                'id' => 80,
                'name' => 'kitchen.inventory',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            80 => 
            array (
                'id' => 81,
                'name' => 'reports.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            81 => 
            array (
                'id' => 82,
                'name' => 'reports.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            82 => 
            array (
                'id' => 83,
                'name' => 'reports.export',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            83 => 
            array (
                'id' => 84,
                'name' => 'reports.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            84 => 
            array (
                'id' => 85,
                'name' => 'reports.sales',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            85 => 
            array (
                'id' => 86,
                'name' => 'reports.financial',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            86 => 
            array (
                'id' => 87,
                'name' => 'reports.inventory',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            87 => 
            array (
                'id' => 88,
                'name' => 'reports.analytics',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            88 => 
            array (
                'id' => 89,
                'name' => 'staff.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            89 => 
            array (
                'id' => 90,
                'name' => 'staff.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            90 => 
            array (
                'id' => 91,
                'name' => 'staff.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            91 => 
            array (
                'id' => 92,
                'name' => 'staff.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            92 => 
            array (
                'id' => 93,
                'name' => 'staff.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            93 => 
            array (
                'id' => 94,
                'name' => 'staff.schedules',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            94 => 
            array (
                'id' => 95,
                'name' => 'staff.permissions',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            95 => 
            array (
                'id' => 96,
                'name' => 'staff.payroll',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            96 => 
            array (
                'id' => 97,
                'name' => 'subscription.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            97 => 
            array (
                'id' => 98,
                'name' => 'subscription.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            98 => 
            array (
                'id' => 99,
                'name' => 'subscription.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            99 => 
            array (
                'id' => 100,
                'name' => 'subscription.billing',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            100 => 
            array (
                'id' => 101,
                'name' => 'subscription.plans',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            101 => 
            array (
                'id' => 102,
                'name' => 'subscription.upgrade',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            102 => 
            array (
                'id' => 103,
                'name' => 'subscription.cancel',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            103 => 
            array (
                'id' => 104,
                'name' => 'subscription.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            104 => 
            array (
                'id' => 105,
                'name' => 'modules.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            105 => 
            array (
                'id' => 106,
                'name' => 'modules.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            106 => 
            array (
                'id' => 107,
                'name' => 'modules.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            107 => 
            array (
                'id' => 108,
                'name' => 'modules.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            108 => 
            array (
                'id' => 109,
                'name' => 'modules.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            109 => 
            array (
                'id' => 110,
                'name' => 'modules.activate',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            110 => 
            array (
                'id' => 111,
                'name' => 'modules.configure',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            111 => 
            array (
                'id' => 112,
                'name' => 'modules.analytics',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            112 => 
            array (
                'id' => 113,
                'name' => 'settings.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            113 => 
            array (
                'id' => 114,
                'name' => 'settings.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            114 => 
            array (
                'id' => 115,
                'name' => 'settings.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            115 => 
            array (
                'id' => 116,
                'name' => 'settings.security',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            116 => 
            array (
                'id' => 117,
                'name' => 'settings.backup',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            117 => 
            array (
                'id' => 118,
                'name' => 'settings.logs',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            118 => 
            array (
                'id' => 119,
                'name' => 'settings.maintenance',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            119 => 
            array (
                'id' => 120,
                'name' => 'settings.integrations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            120 => 
            array (
                'id' => 121,
                'name' => 'production.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            121 => 
            array (
                'id' => 122,
                'name' => 'production.create',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            122 => 
            array (
                'id' => 123,
                'name' => 'production.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            123 => 
            array (
                'id' => 124,
                'name' => 'production.delete',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            124 => 
            array (
                'id' => 125,
                'name' => 'production.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            125 => 
            array (
                'id' => 126,
                'name' => 'production.schedule',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            126 => 
            array (
                'id' => 127,
                'name' => 'production.track',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            127 => 
            array (
                'id' => 128,
                'name' => 'production.reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            128 => 
            array (
                'id' => 129,
                'name' => 'dashboard.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            129 => 
            array (
                'id' => 130,
                'name' => 'dashboard.manage',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            130 => 
            array (
                'id' => 131,
                'name' => 'dashboard.widgets',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            131 => 
            array (
                'id' => 132,
                'name' => 'dashboard.export',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            132 => 
            array (
                'id' => 133,
                'name' => 'organization.view',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            133 => 
            array (
                'id' => 134,
                'name' => 'organization.edit',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            134 => 
            array (
                'id' => 135,
                'name' => 'organization.settings',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            135 => 
            array (
                'id' => 136,
                'name' => 'view_production',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            136 => 
            array (
                'id' => 137,
                'name' => 'view_organizations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            137 => 
            array (
                'id' => 138,
                'name' => 'create_organizations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            138 => 
            array (
                'id' => 139,
                'name' => 'manage_organizations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            139 => 
            array (
                'id' => 140,
                'name' => 'view_branches',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            140 => 
            array (
                'id' => 141,
                'name' => 'activate_branches',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            141 => 
            array (
                'id' => 142,
                'name' => 'manage_subscriptions',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            142 => 
            array (
                'id' => 143,
                'name' => 'manage_roles',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            143 => 
            array (
                'id' => 144,
                'name' => 'manage_modules',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            144 => 
            array (
                'id' => 145,
                'name' => 'create_branches',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            145 => 
            array (
                'id' => 146,
                'name' => 'view_users',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            146 => 
            array (
                'id' => 147,
                'name' => 'view_subscription',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            147 => 
            array (
                'id' => 148,
                'name' => 'edit_organizations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            148 => 
            array (
                'id' => 149,
                'name' => 'manage_staff',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            149 => 
            array (
                'id' => 150,
                'name' => 'manage_schedules',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            150 => 
            array (
                'id' => 151,
                'name' => 'view_operations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            151 => 
            array (
                'id' => 152,
                'name' => 'view-kitchen-orders',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            152 => 
            array (
                'id' => 153,
                'name' => 'manage-kitchen-stations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            153 => 
            array (
                'id' => 154,
                'name' => 'view-kitchen-stations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            154 => 
            array (
                'id' => 155,
                'name' => 'manage-kitchen-staff',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            155 => 
            array (
                'id' => 156,
                'name' => 'manage-reservations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            156 => 
            array (
                'id' => 157,
                'name' => 'take-orders',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            157 => 
            array (
                'id' => 158,
                'name' => 'process-payments',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            158 => 
            array (
                'id' => 159,
                'name' => 'view_inventory',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            159 => 
            array (
                'id' => 160,
                'name' => 'adjust_inventory',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            160 => 
            array (
                'id' => 161,
                'name' => 'pos.access',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            161 => 
            array (
                'id' => 162,
                'name' => 'manage_inventory',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            162 => 
            array (
                'id' => 163,
                'name' => 'manage_suppliers',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            163 => 
            array (
                'id' => 164,
                'name' => 'manage_grn',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            164 => 
            array (
                'id' => 165,
                'name' => 'view_inventory_analytics',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            165 => 
            array (
                'id' => 166,
                'name' => 'view_reservations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            166 => 
            array (
                'id' => 167,
                'name' => 'view_orders',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            167 => 
            array (
                'id' => 168,
                'name' => 'manage-kitchen-production',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            168 => 
            array (
                'id' => 169,
                'name' => 'view_staff',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            169 => 
            array (
                'id' => 170,
                'name' => 'view_reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            170 => 
            array (
                'id' => 171,
                'name' => 'view_advanced_reports',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            171 => 
            array (
                'id' => 172,
                'name' => 'view_analytics',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            172 => 
            array (
                'id' => 173,
                'name' => 'export_data',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            173 => 
            array (
                'id' => 174,
                'name' => 'manage_reservations',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            174 => 
            array (
                'id' => 175,
                'name' => 'manage_orders',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            175 => 
            array (
                'id' => 176,
                'name' => 'view_menu',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            176 => 
            array (
                'id' => 177,
                'name' => 'manage_menu',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            177 => 
            array (
                'id' => 178,
                'name' => 'manage_branches',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            178 => 
            array (
                'id' => 179,
                'name' => 'manage_organization',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            179 => 
            array (
                'id' => 180,
                'name' => 'view_billing',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            180 => 
            array (
                'id' => 181,
                'name' => 'manage_payments',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
            181 => 
            array (
                'id' => 182,
                'name' => 'manage_settings',
                'guard_name' => 'admin',
                'created_at' => '2025-07-21 04:04:24',
                'updated_at' => '2025-07-21 04:04:24',
            ),
        ));
        
        
    }
}