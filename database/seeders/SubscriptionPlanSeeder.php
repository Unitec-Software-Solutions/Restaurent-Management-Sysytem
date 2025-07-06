<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\Module;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating subscription plans...');

        // Get all modules for reference
        $modules = Module::all();
        $moduleIds = $modules->pluck('id')->toArray();

        $plans = [
            [
                'name' => 'Basic Plan',
                'description' => 'Essential features for small restaurants',
                'price' => 4999, // $49.99
                'currency' => 'USD',
                'max_branches' => 1,
                'max_employees' => 10,
                'trial_period_days' => 14,
                'modules' => array_slice($moduleIds, 0, 3), // First 3 modules
                'features' => [
                    'basic_pos',
                    'order_management',
                    'basic_inventory',
                    'basic_reporting',
                    'customer_management'
                ],
                'is_trial' => false,
                'is_active' => true
            ],
            [
                'name' => 'Professional Plan',
                'description' => 'Advanced features for growing restaurants',
                'price' => 9999, // $99.99
                'currency' => 'USD',
                'max_branches' => 5,
                'max_employees' => 50,
                'trial_period_days' => 30,
                'modules' => array_slice($moduleIds, 0, 6), // First 6 modules
                'features' => [
                    'advanced_pos',
                    'order_management',
                    'advanced_inventory',
                    'table_reservations',
                    'staff_management',
                    'advanced_reporting',
                    'customer_loyalty',
                    'multi_branch_support'
                ],
                'is_trial' => false,
                'is_active' => true
            ],
            [
                'name' => 'Enterprise Plan',
                'description' => 'Complete solution for restaurant chains',
                'price' => 19999, // $199.99
                'currency' => 'USD',
                'max_branches' => null, // Unlimited
                'max_employees' => null, // Unlimited
                'trial_period_days' => 30,
                'modules' => $moduleIds, // All modules
                'features' => [
                    'enterprise_pos',
                    'advanced_order_management',
                    'complete_inventory_suite',
                    'advanced_reservations',
                    'comprehensive_staff_management',
                    'business_intelligence',
                    'customer_analytics',
                    'multi_location_management',
                    'api_access',
                    'custom_integrations',
                    'priority_support'
                ],
                'is_trial' => false,
                'is_active' => true
            ],
            [
                'name' => 'Trial Plan',
                'description' => '14-day free trial with basic features',
                'price' => 0, // Free
                'currency' => 'USD',
                'max_branches' => 1,
                'max_employees' => 5,
                'trial_period_days' => 14,
                'modules' => array_slice($moduleIds, 0, 2), // First 2 modules only
                'features' => [
                    'basic_pos',
                    'order_management',
                    'basic_inventory',
                    'basic_reporting'
                ],
                'is_trial' => true,
                'is_active' => true
            ]
        ];

        foreach ($plans as $planData) {
            $plan = SubscriptionPlan::create($planData);
            $this->command->info("  ✓ Created plan: {$plan->name}");
        }

        $this->command->info('✅ Subscription plans created successfully!');
    }
}
