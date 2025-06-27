<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\Module;
use Carbon\Carbon;

/**
 * Exhaustive Subscription Plan Seeder
 * 
 * Creates comprehensive subscription scenarios:
 * - Basic/Freemium plans with limited modules
 * - Premium plans with all modules
 * - Expired/disabled plans
 * - Trial plans with various durations
 * - Enterprise plans with advanced features
 * - Legacy plans for migration testing
 * - Custom plans for specific use cases
 */
class ExhaustiveSubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  📋 Creating subscription plan scenarios...');

        // Create comprehensive subscription plans
        $plans = [
            // 1. FREEMIUM/BASIC PLANS
            [
                'name' => 'Freemium',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'basic'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                ],
                'features' => ['basic_pos', 'basic_kitchen', 'single_branch'],
                'price' => 0,
                'currency' => 'LKR',
                'description' => 'Free starter plan with basic POS and kitchen features',
                'is_trial' => false,
                'trial_period_days' => 0,
                'max_branches' => 1,
                'max_employees' => 5,
                'is_active' => true,
            ],
            
            [
                'name' => 'Basic',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'basic'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                    ['name' => 'reservations', 'tier' => 'basic'],
                ],
                'features' => ['basic_pos', 'basic_kitchen', 'table_reservations', 'basic_reporting'],
                'price' => 2500,
                'currency' => 'LKR',
                'description' => 'Essential features for small restaurants',
                'is_trial' => false,
                'trial_period_days' => 14,
                'max_branches' => 2,
                'max_employees' => 10,
                'is_active' => true,
            ],

            // 2. TRIAL PLANS (Various Durations)
            [
                'name' => 'Pro Trial',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'premium'],
                    ['name' => 'inventory', 'tier' => 'premium'],
                    ['name' => 'reservations', 'tier' => 'premium'],
                    ['name' => 'staff', 'tier' => 'premium'],
                ],
                'features' => ['all_premium_features', 'advanced_analytics', 'multi_branch'],
                'price' => 0,
                'currency' => 'LKR',
                'description' => '30-day trial of Pro features',
                'is_trial' => true,
                'trial_period_days' => 30,
                'max_branches' => 5,
                'max_employees' => 25,
                'is_active' => true,
            ],

            [
                'name' => 'Enterprise Trial',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'enterprise'],
                    ['name' => 'kitchen', 'tier' => 'enterprise'],
                    ['name' => 'inventory', 'tier' => 'enterprise'],
                    ['name' => 'reservations', 'tier' => 'enterprise'],
                    ['name' => 'staff', 'tier' => 'enterprise'],
                    ['name' => 'analytics', 'tier' => 'enterprise'],
                    ['name' => 'customer', 'tier' => 'enterprise'],
                ],
                'features' => ['all_enterprise_features', 'white_label', 'api_access', 'unlimited_branches'],
                'price' => 0,
                'currency' => 'LKR',
                'description' => '14-day enterprise trial for large chains',
                'is_trial' => true,
                'trial_period_days' => 14,
                'max_branches' => 999,
                'max_employees' => 999,
                'is_active' => true,
            ],

            // 3. PREMIUM PRODUCTION PLANS
            [
                'name' => 'Professional',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'premium'],
                    ['name' => 'inventory', 'tier' => 'premium'],
                    ['name' => 'reservations', 'tier' => 'premium'],
                    ['name' => 'staff', 'tier' => 'premium'],
                    ['name' => 'reporting', 'tier' => 'premium'],
                ],
                'features' => ['advanced_pos', 'kitchen_display', 'inventory_management', 'staff_scheduling', 'analytics'],
                'price' => 7500,
                'currency' => 'LKR',
                'description' => 'Full-featured plan for growing restaurants',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 5,
                'max_employees' => 50,
                'is_active' => true,
            ],

            [
                'name' => 'Enterprise',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'enterprise'],
                    ['name' => 'kitchen', 'tier' => 'enterprise'],
                    ['name' => 'inventory', 'tier' => 'enterprise'],
                    ['name' => 'reservations', 'tier' => 'enterprise'],
                    ['name' => 'staff', 'tier' => 'enterprise'],
                    ['name' => 'analytics', 'tier' => 'enterprise'],
                    ['name' => 'customer', 'tier' => 'enterprise'],
                    ['name' => 'finance', 'tier' => 'enterprise'],
                ],
                'features' => ['all_features', 'multi_location', 'advanced_analytics', 'api_access', 'white_label', 'priority_support'],
                'price' => 25000,
                'currency' => 'LKR',
                'description' => 'Complete solution for restaurant chains and franchises',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 50,
                'max_employees' => 500,
                'is_active' => true,
            ],

            // 4. LEGACY PLANS (For Migration Testing)
            [
                'name' => 'Legacy Standard',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'basic'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                    ['name' => 'inventory', 'tier' => 'basic'],
                ],
                'features' => ['legacy_pos', 'basic_kitchen', 'simple_inventory'],
                'price' => 5000,
                'currency' => 'LKR',
                'description' => 'Legacy plan for existing customers (deprecated)',
                'is_trial' => false,
                'trial_period_days' => 0,
                'max_branches' => 3,
                'max_employees' => 20,
                'is_active' => false, // Disabled for new signups
            ],

            // 5. EXPIRED PLANS (For Testing)
            [
                'name' => 'Expired Premium',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'premium'],
                    ['name' => 'inventory', 'tier' => 'premium'],
                ],
                'features' => ['premium_features'],
                'price' => 6000,
                'currency' => 'LKR',
                'description' => 'Expired premium plan for testing downgrade scenarios',
                'is_trial' => false,
                'trial_period_days' => 0,
                'max_branches' => 4,
                'max_employees' => 30,
                'is_active' => false, // Expired/disabled
            ],

            // 6. CUSTOM PLANS FOR SPECIFIC USE CASES
            [
                'name' => 'Seasonal Restaurant',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                    ['name' => 'reservations', 'tier' => 'premium'],
                ],
                'features' => ['seasonal_menus', 'event_management', 'peak_season_analytics'],
                'price' => 4000,
                'currency' => 'LKR',
                'description' => 'Specialized plan for seasonal operations',
                'is_trial' => false,
                'trial_period_days' => 7,
                'max_branches' => 2,
                'max_employees' => 15,
                'is_active' => true,
            ],

            [
                'name' => 'Food Truck',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                ],
                'features' => ['mobile_pos', 'location_tracking', 'simplified_operations'],
                'price' => 3000,
                'currency' => 'LKR',
                'description' => 'Mobile-optimized plan for food trucks and mobile vendors',
                'is_trial' => false,
                'trial_period_days' => 14,
                'max_branches' => 1,
                'max_employees' => 5,
                'is_active' => true,
            ],

            [
                'name' => 'Franchise Master',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'enterprise'],
                    ['name' => 'kitchen', 'tier' => 'enterprise'],
                    ['name' => 'inventory', 'tier' => 'enterprise'],
                    ['name' => 'reservations', 'tier' => 'enterprise'],
                    ['name' => 'staff', 'tier' => 'enterprise'],
                    ['name' => 'analytics', 'tier' => 'enterprise'],
                    ['name' => 'customer', 'tier' => 'enterprise'],
                    ['name' => 'finance', 'tier' => 'enterprise'],
                    ['name' => 'franchise', 'tier' => 'enterprise'],
                ],
                'features' => ['franchise_management', 'multi_brand', 'centralized_control', 'franchise_analytics', 'royalty_tracking'],
                'price' => 50000,
                'currency' => 'LKR',
                'description' => 'Ultimate plan for franchise operations with centralized management',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 999,
                'max_employees' => 9999,
                'is_active' => true,
            ],

            // 7. PLAN UPGRADE/DOWNGRADE SCENARIOS
            [
                'name' => 'Startup Special',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'basic'],
                    ['name' => 'reservations', 'tier' => 'basic'],
                ],
                'features' => ['startup_discount', 'growth_ready', 'easy_upgrade'],
                'price' => 1500,
                'currency' => 'LKR',
                'description' => 'Special pricing for new restaurants (first 6 months)',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 2,
                'max_employees' => 10,
                'is_active' => true,
            ],

            // 8. INTERNATIONAL PLANS (Different Currencies)
            [
                'name' => 'International Basic',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'premium'],
                    ['name' => 'kitchen', 'tier' => 'premium'],
                    ['name' => 'reservations', 'tier' => 'basic'],
                ],
                'features' => ['multi_currency', 'international_support', 'timezone_management'],
                'price' => 50,
                'currency' => 'USD',
                'description' => 'Basic plan for international markets',
                'is_trial' => false,
                'trial_period_days' => 14,
                'max_branches' => 3,
                'max_employees' => 20,
                'is_active' => true,
            ],

            [
                'name' => 'International Premium',
                'modules' => [
                    ['name' => 'pos', 'tier' => 'enterprise'],
                    ['name' => 'kitchen', 'tier' => 'enterprise'],
                    ['name' => 'inventory', 'tier' => 'enterprise'],
                    ['name' => 'reservations', 'tier' => 'enterprise'],
                    ['name' => 'staff', 'tier' => 'enterprise'],
                    ['name' => 'analytics', 'tier' => 'enterprise'],
                ],
                'features' => ['all_features', 'multi_currency', 'international_compliance', '24_7_support'],
                'price' => 200,
                'currency' => 'USD',
                'description' => 'Premium plan for international restaurant chains',
                'is_trial' => false,
                'trial_period_days' => 30,
                'max_branches' => 25,
                'max_employees' => 250,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            $plan = SubscriptionPlan::create($planData);
            $this->command->info("    ✓ Created plan: {$plan->name} ({$plan->currency} {$plan->price})");
        }

        $this->command->info("  ✅ Created " . count($plans) . " subscription plan scenarios covering all use cases");
    }
}
