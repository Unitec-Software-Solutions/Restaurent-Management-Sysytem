<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPlan;
use App\Models\Organization;
use App\Models\Subscription;
use Carbon\Carbon;

class ExhaustiveSubscriptionSeeder extends Seeder
{
    /**
     * Seed comprehensive subscription plan scenarios covering all possible cases
     */
    public function run(): void
    {
        $this->command->info('💳 Creating exhaustive subscription plan scenarios...');
        
        // Clear existing plans
        SubscriptionPlan::truncate();
        
        // 1. Basic/Freemium Plans with Limited Modules
        $this->createBasicPlans();
        
        // 2. Premium Plans with Full Feature Access  
        $this->createPremiumPlans();
        
        // 3. Enterprise Plans for Large Organizations
        $this->createEnterprisePlans();
        
        // 4. Trial Plans with Time Limitations
        $this->createTrialPlans();
        
        // 5. Expired/Disabled Plans for Testing Scenarios
        $this->createExpiredPlans();
        
        // 6. Custom Plans for Special Cases
        $this->createCustomPlans();
        
        $this->command->info('  ✅ Created ' . SubscriptionPlan::count() . ' subscription plans with all scenarios');
    }
    
    private function createBasicPlans(): void
    {
        // Basic plan - minimal features for small restaurants
        SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Perfect for single-location cafes and small restaurants',
            'price' => 0.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 1,
            'max_employees' => 5,
            'max_menu_items' => 50,
            'max_tables' => 10,
            'modules' => json_encode([
                ['name' => 'pos', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'basic', 'enabled' => false],
                ['name' => 'inventory', 'tier' => 'basic', 'enabled' => false],
                ['name' => 'reporting', 'tier' => 'basic', 'enabled' => false],
                ['name' => 'analytics', 'tier' => 'basic', 'enabled' => false],
            ]),
            'features' => json_encode([
                'basic_pos',
                'simple_menu_management',
                'order_tracking',
                'basic_kitchen_display',
                'email_support'
            ]),
            'restrictions' => json_encode([
                'no_multi_location',
                'limited_integrations',
                'basic_reporting_only',
                'no_advanced_analytics'
            ]),
            'is_active' => true,
        ]);
        
        // Freemium plan - free tier with heavy restrictions
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Free tier for testing and very small operations',
            'price' => 0.00,
            'currency' => 'LKR',
            'billing_cycle' => 'one_time',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 1,
            'max_employees' => 2,
            'max_menu_items' => 20,
            'max_tables' => 5,
            'max_orders_per_day' => 50,
            'modules' => [
                ['name' => 'pos', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'basic', 'enabled' => false],
                ['name' => 'menu', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'basic', 'enabled' => true],
            ],
            'features' => [
                'basic_pos_only',
                'simple_menu',
                'order_queue',
                'community_support'
            ],
            'restrictions' => json_encode([
                'watermarked_receipts',
                'limited_daily_orders',
                'no_customer_management',
                'no_reporting',
                'no_inventory_tracking'
            ]),
            'is_active' => true,
        ]);
    }
    
    private function createPremiumPlans(): void
    {
        // Professional plan - full features for growing restaurants
        SubscriptionPlan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Complete restaurant management solution for growing businesses',
            'price' => 8500.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 3,
            'max_employees' => 25,
            'max_menu_items' => 200,
            'max_tables' => 50,
            'modules' => [
                ['name' => 'pos', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'suppliers', 'tier' => 'premium', 'enabled' => true],
            ],
            'features' => [
                'advanced_pos',
                'full_inventory_management',
                'customer_loyalty_programs',
                'advanced_reporting',
                'staff_scheduling',
                'supplier_management',
                'multi_location_support',
                'api_integrations',
                'priority_support'
            ],
            'restrictions' => json_encode(['limited_to_3_branches',
                'standard_customization'
            ]),
            'is_active' => true,
        ]);
        
        // Premium Plus - enhanced professional features
        SubscriptionPlan::create([
            'name' => 'Premium Plus',
            'slug' => 'premium-plus',
            'description' => 'Enhanced professional features with advanced analytics',
            'price' => 12500.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 5,
            'max_employees' => 50,
            'max_menu_items' => 500,
            'max_tables' => 100,
            'modules' => [
                ['name' => 'pos', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'analytics', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'suppliers', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'marketing', 'tier' => 'premium', 'enabled' => true],
            ],
            'features' => [
                'all_professional_features',
                'advanced_analytics_dashboard',
                'predictive_inventory',
                'marketing_automation',
                'custom_branding',
                'white_label_options',
                'dedicated_account_manager'
            ],
            'restrictions' => json_encode([]),
            'is_active' => true,
        ]);
    }
    
    private function createEnterprisePlans(): void
    {
        // Enterprise plan - unlimited features for large chains
        SubscriptionPlan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Unlimited features for restaurant chains and large operations',
            'price' => 25000.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 999,
            'max_employees' => 999,
            'max_menu_items' => 9999,
            'max_tables' => 999,
            'modules' => [
                ['name' => 'pos', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'analytics', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'suppliers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'marketing', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'finance', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'franchise', 'tier' => 'enterprise', 'enabled' => true],
            ],
            'features' => [
                'unlimited_everything',
                'enterprise_analytics',
                'franchise_management',
                'advanced_financial_reporting',
                'custom_integrations',
                'api_access',
                'sso_integration',
                'advanced_security',
                '24_7_support',
                'onsite_training',
                'custom_development'
            ],
            'restrictions' => json_encode([]),
            'is_active' => true,
        ]);
        
        // Enterprise Pro - maximum features with custom development
        SubscriptionPlan::create([
            'name' => 'Enterprise Pro',
            'slug' => 'enterprise-pro',
            'description' => 'Ultimate solution with custom development and dedicated infrastructure',
            'price' => 50000.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => null, // Unlimited
            'max_employees' => null,
            'max_menu_items' => null,
            'max_tables' => null,
            'modules' => [
                ['name' => 'pos', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'analytics', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'suppliers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'marketing', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'finance', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'franchise', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'custom', 'tier' => 'enterprise', 'enabled' => true],
            ],
            'features' => [
                'all_enterprise_features',
                'dedicated_infrastructure',
                'custom_module_development',
                'advanced_ai_analytics',
                'predictive_forecasting',
                'blockchain_integration',
                'iot_device_support',
                'dedicated_support_team',
                'quarterly_business_reviews'
            ],
            'restrictions' => json_encode([]),
            'is_active' => true,
        ]);
    }
    
    private function createTrialPlans(): void
    {
        // 14-day trial of Professional
        SubscriptionPlan::create([
            'name' => 'Professional Trial',
            'slug' => 'professional-trial',
            'description' => '14-day free trial of Professional features',
            'price' => 0.00,
            'currency' => 'LKR',
            'billing_cycle' => 'one_time',
            'is_trial' => true,
            'trial_period_days' => 14,
            'max_branches' => 3,
            'max_employees' => 25,
            'max_menu_items' => 200,
            'max_tables' => 50,
            'modules' => [
                ['name' => 'pos', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'premium', 'enabled' => true],
            ],
            'features' => [
                'all_professional_features',
                'trial_support',
                'onboarding_assistance'
            ],
            'restrictions' => json_encode(['trial_watermarks',
                'expires_after_14_days',
                'limited_data_export'
            ]),
            'is_active' => true,
        ]);
        
        // 30-day Enterprise trial
        SubscriptionPlan::create([
            'name' => 'Enterprise Trial',
            'slug' => 'enterprise-trial',
            'description' => '30-day free trial of Enterprise features',
            'price' => 0.00,
            'currency' => 'LKR',
            'billing_cycle' => 'one_time',
            'is_trial' => true,
            'trial_period_days' => 30,
            'max_branches' => 999,
            'max_employees' => 999,
            'max_menu_items' => 9999,
            'max_tables' => 999,
            'modules' => [
                ['name' => 'pos', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'analytics', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'suppliers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'marketing', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'finance', 'tier' => 'enterprise', 'enabled' => true],
            ],
            'features' => [
                'all_enterprise_features',
                'dedicated_trial_support',
                'migration_assistance'
            ],
            'restrictions' => json_encode(['trial_watermarks',
                'expires_after_30_days',
                'limited_integrations'
            ]),
            'is_active' => true,
        ]);
    }
    
    private function createExpiredPlans(): void
    {
        // Legacy plan - deprecated but still in use
        SubscriptionPlan::create([
            'name' => 'Legacy Basic',
            'slug' => 'legacy-basic',
            'description' => 'Deprecated plan for existing customers only',
            'price' => 3500.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 1,
            'max_employees' => 10,
            'max_menu_items' => 100,
            'max_tables' => 25,
            'modules' => [
                ['name' => 'pos', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'basic', 'enabled' => true],
            ],
            'features' => [
                'legacy_features',
                'limited_support'
            ],
            'restrictions' => json_encode(['no_new_signups',
                'deprecated_features',
                'limited_updates'
            ]),
            'is_active' => false, // Disabled for new signups
        ]);
        
        // Suspended plan
        SubscriptionPlan::create([
            'name' => 'Suspended Account',
            'slug' => 'suspended',
            'description' => 'Plan for suspended accounts',
            'price' => 0.00,
            'currency' => 'LKR',
            'billing_cycle' => 'one_time',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 0,
            'max_employees' => 0,
            'max_menu_items' => 0,
            'max_tables' => 0,
            'modules' => [],
            'features' => [
                'read_only_access',
                'data_export_only'
            ],
            'restrictions' => json_encode(['no_new_data_creation',
                'no_order_processing',
                'limited_system_access'
            ]),
            'is_active' => false,
        ]);
    }
    
    private function createCustomPlans(): void
    {
        // Seasonal plan for temporary operations
        SubscriptionPlan::create([
            'name' => 'Seasonal',
            'slug' => 'seasonal',
            'description' => 'Perfect for seasonal restaurants and pop-up operations',
            'price' => 5500.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 2,
            'max_employees' => 15,
            'max_menu_items' => 100,
            'max_tables' => 30,
            'modules' => [
                ['name' => 'pos', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'basic', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'premium', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'basic', 'enabled' => true],
            ],
            'features' => [
                'quick_setup',
                'flexible_billing',
                'seasonal_analytics',
                'popup_support'
            ],
            'restrictions' => json_encode(['limited_historical_data',
                'basic_integrations_only'
            ]),
            'is_active' => true,
        ]);
        
        // Franchise plan with special features
        SubscriptionPlan::create([
            'name' => 'Franchise Master',
            'slug' => 'franchise-master',
            'description' => 'Specialized plan for franchise operations with central management',
            'price' => 35000.00,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'is_trial' => false,
            'trial_period_days' => null,
            'max_branches' => 50,
            'max_employees' => 500,
            'max_menu_items' => 5000,
            'max_tables' => 1000,
            'modules' => [
                ['name' => 'pos', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'kitchen', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'menu', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'orders', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'customers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'inventory', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reservations', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'reporting', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'analytics', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'staff', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'suppliers', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'marketing', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'finance', 'tier' => 'enterprise', 'enabled' => true],
                ['name' => 'franchise', 'tier' => 'enterprise', 'enabled' => true],
            ],
            'features' => [
                'centralized_management',
                'franchise_analytics',
                'royalty_tracking',
                'brand_compliance_monitoring',
                'multi_tenant_architecture',
                'franchise_portal',
                'territory_management'
            ],
            'restrictions' => json_encode([]),
            'is_active' => true,
        ]);
    }
}
