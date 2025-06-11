<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            ['name' => 'menu_management', 'description' => 'Manage restaurant menu'],
            ['name' => 'inventory', 'description' => 'Inventory management'],
            ['name' => 'reporting', 'description' => 'Analytics and reports'],
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
