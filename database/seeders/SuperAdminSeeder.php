<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👑 Creating/updating Super Admin...');
        
        $admin = Admin::updateOrCreate(
            ['email' => 'superadmin@rms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), 
                'is_super_admin' => true, 
                'is_active' => true,
            ]
        );
        
        $this->command->info("  ✅ Super Admin: {$admin->email}");
        
        // Ensure Super Admin role exists first
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'organization_id' => null,
            'guard_name' => 'admin',
        ]);
        
        // Assign Super Admin role if not already assigned
        if (!$admin->hasRole($superAdminRole->name, 'admin')) {
            $admin->assignRole($superAdminRole);
            $this->command->info("  ✅ Assigned Super Admin role");
        } else {
            $this->command->info("  ✅ Super Admin role already assigned");
        }
        
        $this->command->info('✅ Super Admin setup completed');
    }
}
