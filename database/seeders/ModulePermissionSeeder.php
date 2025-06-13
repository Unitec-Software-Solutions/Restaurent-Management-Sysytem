<?php
// database/seeders/ModulePermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Spatie\Permission\Models\Permission;

class ModulePermissionSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            'reservation' => ['create', 'view', 'edit', 'delete', 'manage'],
            'order' => ['create', 'process', 'cancel', 'refund', 'report'],
            'inventory' => ['manage', 'adjust', 'audit', 'supplier']
        ];

        foreach ($modules as $moduleName => $actions) {
            $module = Module::create([
                'name' => $moduleName,
                'description' => ucfirst($moduleName) . ' module'
            ]);
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate([
                    'name' => "{$action}_{$moduleName}",
                    'guard_name' => 'web'
                ]);
                $module->permissions()->attach($permission);
            }
        }
    }
}