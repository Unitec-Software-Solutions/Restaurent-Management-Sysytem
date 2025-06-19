<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\Organizations;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $organizations = Organizations::all();

        // if ($organizations->count() < 5) {
        //     $this->command->warn('🚨 Not enough organizations found. Ensure 5 organizations exist before seeding admins.');
        //     return;
        // }

        // /**  
        //  * Creates 2 admins per organization with sequentially numbered credentials:  
        //  * - Email: admin{number}@example.com (e.g., admin1@example.com, admin2@example.com, ...)  
        //  * - Password: password{number} (e.g., password1, password2, ...)  
        //  *  
        //  * Example: The 5th admin will have:  
        //  * - Email: admin5@example.com  
        //  * - Password: password5  
        //  */  

        // $adminIndex = 1;

        // foreach ($organizations as $organization) {
        //     $branches = Branch::where('organization_id', $organization->id)->get();

        //     if ($branches->isEmpty()) {
        //         $this->command->warn("⚠️ No branches found for organization ID {$organization->id}. Skipping admin creation.");
        //         continue;
        //     }

        //     for ($i = 0; $i < 2; $i++) { // Two admins per organization
        //         $branch = $branches->random();

        //         $email = "admin{$adminIndex}@example.com";

        //         Admin::firstOrCreate(
        //             ['email' => $email],
        //             [
        //                 'name' => "Admin User $adminIndex",
        //                 'password' => Hash::make("password{$adminIndex}"),
        //                 'branch_id' => $branch->id,
        //                 'organization_id' => $organization->id,
        //             ]
        //         );

        //         $adminIndex++;
        //     }
        // }

        // Additional testing admin
        // $defaultBranch = Branch::first();
        // $defaultOrg = $defaultBranch ? $defaultBranch->organization_id : null;

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@rms.com'],
            [
                'name' => 'Testing Admin',
                'password' => Hash::make('admin123'),
                'is_super_admin' => true,
            ]
        );

        // Make sure the role exists
        $role = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'admin']
        );

        // Assign the role to the admin
        $admin->assignRole($role);

        $this->command->info('  ✅ 10 Admin users and 1 testing admin seeded successfully (skipped existing ones).');
    }
}
