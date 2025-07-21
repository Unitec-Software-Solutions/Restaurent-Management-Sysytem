<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\MinimalSystemSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database - Minimal Setup
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting minimal database seeding...');

        // Only essential seeders for basic system functionality
        $this->call([
            MinimalSystemSeeder::class,
        ]);

        $this->command->info('✅ Minimal seeding completed successfully');
        $this->command->info('🔐 Login at /admin/login with: superadmin@rms.com / SuperAdmin123!');
        // $this->call(OrganizationsTableSeeder::class);
        // $this->call(BranchesTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
    }
}
