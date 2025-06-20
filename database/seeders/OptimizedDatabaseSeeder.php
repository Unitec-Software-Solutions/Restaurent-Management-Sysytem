<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class OptimizedDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with optimized test data.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Optimized Restaurant Management System Seeding...');
        $this->command->newLine();

        // Use existing subscription plan seeder
        $this->call([
            SubscriptionPlanSeeder::class,
            OptimizedOrganizationSeeder::class,
            OptimizedBranchSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            ItemCategorySeeder::class,
            ItemMasterSeeder::class,
            MenuItemSeeder::class,
            TableSeeder::class,
            SupplierSeeder::class,
            ReservationSeeder::class,
            ComprehensiveTestSeeder::class, // Our comprehensive test seeder
        ]);

        $this->command->newLine();
        $this->command->info('✅ Optimized seeding completed successfully!');
        $this->command->newLine();
        
        $this->command->line('🎯 <fg=green>Test Credentials:</fg=green>');
        $this->command->line('   • Super Admin: superadmin@rms.com / password123');
        $this->command->line('   • Org Admin 1: admin1@spicegarden.com / password123');
        $this->command->line('   • Org Admin 2: admin2@oceanview.com / password123');
        $this->command->line('   • Org Admin 3: admin3@hillkitchen.com / password123');
        
        $this->command->newLine();
        $this->command->line('📋 <fg=cyan>Organizations Created:</fg=cyan>');
        $this->command->line('   1. Spice Garden Restaurant (Enterprise Plan) - 2 branches');
        $this->command->line('   2. Ocean View Cafe (Pro Plan) - 2 branches');
        $this->command->line('   3. Hill Country Kitchen (Basic Plan) - 2 branches');
        
        $this->command->newLine();
        $this->command->line('🧪 <fg=yellow>Ready for Testing:</fg=yellow>');
        $this->command->line('   • Module activation/deactivation workflows');
        $this->command->line('   • Subscription tier limitations');
        $this->command->line('   • Order-to-kitchen (KOT) workflows');
        $this->command->line('   • Inventory alerts and management');
        $this->command->line('   • Role-based permissions');
        $this->command->line('   • Real-time kitchen operations');
    }
}
