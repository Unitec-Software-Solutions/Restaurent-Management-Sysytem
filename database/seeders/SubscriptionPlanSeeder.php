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
        SubscriptionPlan::firstOrCreate([
            'name' => 'Basic',
        ], [
            'modules' => json_encode([]),
            'price' => 0,
            'currency' => 'LKR',
            'description' => 'Basic free plan',
            'is_trial' => true,
            'trial_period_days' => 30,
        ]);

        SubscriptionPlan::firstOrCreate([
            'name' => 'Pro',
        ], [
            'modules' => json_encode([]),
            'price' => 5000,
            'currency' => 'LKR',
            'description' => 'Pro annual plan',
            'is_trial' => false,
            'trial_period_days' => null,
        ]);

        SubscriptionPlan::firstOrCreate([
            'name' => 'Legacy',
        ], [
            'modules' => json_encode([]),
            'price' => 2000,
            'currency' => 'LKR',
            'description' => 'Legacy plan (inactive)',
            'is_trial' => false,
            'trial_period_days' => null,
        ]);
    }
}
