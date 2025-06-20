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
            'features' => [
                'basic_ordering',
                'cash_payments',
                'receipt_printing',
                'kot_display',
                'basic_status_updates',
                'basic_booking',
                'table_assignment',
                'daily_sales',
                'basic_charts'
            ],
            'price' => 0,
            'currency' => 'LKR',
            'description' => 'Basic plan for small restaurants - Essential POS and kitchen management',
            'is_trial' => true,
            'trial_period_days' => 30,
            'max_branches' => 2,
            'max_employees' => 10,
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
            'features' => [
                'basic_ordering', 'cash_payments', 'receipt_printing',
                'split_billing', 'discount_management', 'loyalty_points', 'detailed_analytics',
                'kot_display', 'basic_status_updates', 'advanced_timing', 'kitchen_reports', 'recipe_costing',
                'automated_reordering', 'waste_analytics', 'supplier_performance', 'cost_analysis',
                'basic_booking', 'table_assignment', 'advanced_scheduling', 'guest_history', 'automated_reminders',
                'performance_analytics', 'automated_scheduling', 'labor_cost_tracking',
                'predictive_analytics', 'custom_dashboards', 'automated_reporting',
                'loyalty_programs', 'customer_segmentation', 'feedback_analysis'
            ],
            'price' => 5000,
            'currency' => 'LKR',
            'description' => 'Professional plan for growing restaurants - Full featured management suite',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 10,
            'max_employees' => 50,
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
            'features' => [
                'basic_ordering',
                'cash_payments',
                'receipt_printing',
                'kot_display',
                'basic_status_updates',
                'basic_stock_view',
                'manual_updates',
                'basic_booking',
                'table_assignment',
                'daily_sales',
                'basic_charts'
            ],
            'price' => 2000,
            'currency' => 'LKR',
            'description' => 'Legacy plan for existing customers - Transitional features',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 5,
            'max_employees' => 25,
        ]);

        $this->command->info('  ✅ Subscription plans seeded with module configurations');
    }
}
