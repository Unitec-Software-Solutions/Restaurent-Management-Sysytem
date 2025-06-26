<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        Module::insert([
            ['name' => 'menu_management', 'description' => 'Manage restaurant menu'],
            ['name' => 'staff_scheduling', 'description' => 'Manage employee shifts'],
            ['name' => 'billing', 'description' => 'Handle customer billing'],
            ['name' => 'inventory_management', 'description' => 'Manage inventory and stock'],
        ]);
    }
}
