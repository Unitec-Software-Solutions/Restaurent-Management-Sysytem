<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing plans
        SubscriptionPlan::truncate();

        SubscriptionPlan::create([
            'name' => 'Basic',
            'modules' => [
                ['name' => 'pos', 'tier' => 'basic'],
                ['name' => 'kitchen', 'tier' => 'basic'],
                ['name' => 'reservations', 'tier' => 'basic'],
                ['name' => 'reporting', 'tier' => 'basic'],
            ],
            'price' => 0,
            'currency' => 'LKR',
            'description' => 'Basic plan for small restaurants - Essential POS and kitchen management',
            'is_trial' => true,
            'trial_period_days' => 30,
        ]);

        SubscriptionPlan::create([
            'name' => 'Pro',
            'modules' => [
                ['name' => 'pos', 'tier' => 'premium'],
                ['name' => 'kitchen', 'tier' => 'premium'],
                ['name' => 'inventory', 'tier' => 'premium'],
                ['name' => 'reservations', 'tier' => 'premium'],
                ['name' => 'staff', 'tier' => 'premium'],
                ['name' => 'reporting', 'tier' => 'premium'],
                ['name' => 'customer', 'tier' => 'premium'],
            ],
            'price' => 5000,
            'currency' => 'LKR',
            'description' => 'Professional plan for growing restaurants - Full featured management suite',
            'is_trial' => false,
            'trial_period_days' => null,
        ]);

        SubscriptionPlan::create([
            'name' => 'Legacy',
            'modules' => [
                ['name' => 'pos', 'tier' => 'basic'],
                ['name' => 'kitchen', 'tier' => 'basic'],
                ['name' => 'inventory', 'tier' => 'basic'],
                ['name' => 'reservations', 'tier' => 'basic'],
                ['name' => 'reporting', 'tier' => 'basic'],
            ],
            'price' => 2000,
            'currency' => 'LKR',
            'description' => 'Legacy plan for existing customers - Transitional features',
            'is_trial' => false,
            'trial_period_days' => null,
        ]);

        $this->command->info('  ✅ Subscription plans seeded with module configurations');
    }
}
