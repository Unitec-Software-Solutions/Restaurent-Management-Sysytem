<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class RefreshSuperAdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Refreshing Super Admin permissions...');

        // 1. Get or create the Super Admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Administrator',
            'guard_name' => 'admin'
        ]);

        // 2. Get ALL permissions for admin guard
        $allPermissions = Permission::where('guard_name', 'admin')->get();

        // 3. Sync ALL permissions to Super Admin role
        $superAdminRole->syncPermissions($allPermissions);

        // 4. Find all super admin users
        $superAdmins = Admin::where('is_super_admin', true)->get();

        // 5. Ensure each super admin has the role and all permissions
        foreach ($superAdmins as $admin) {
            $admin->assignRole($superAdminRole);

            // Also set is_super_admin flag just in case
            if (!$admin->is_super_admin) {
                $admin->update(['is_super_admin' => true]);
            }
        }

        $this->command->info("✅ Refreshed permissions:");
        $this->command->info("   • Role: Super Administrator");
        $this->command->info("   • Total Permissions: " . $allPermissions->count());
        $this->command->info("   • Super Admins Updated: " . $superAdmins->count());

        Log::info('Super Admin permissions refreshed', [
            'permissions_count' => $allPermissions->count(),
            'admins_updated' => $superAdmins->count()
        ]);
    }
}
