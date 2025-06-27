<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExhaustiveOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  🏢 Creating organization onboarding variations...');

        // Get subscription plans
        $plans = SubscriptionPlan::all()->keyBy('name');

        // 1. SINGLE-BRANCH OPERATIONS
        $singleBranchOrgs = [
            [
                'name' => 'Maya\'s Kitchen',
                'trading_name' => 'Maya\'s',
                'registration_number' => 'SB001',
                'email' => 'admin@mayaskitchen.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 234 5678',
                'address' => '45 Galle Road, Colombo 03',
                'website' => 'https://mayaskitchen.lk',
                'contact_person' => 'Maya Perera',
                'contact_person_designation' => 'Owner & Chef',
                'contact_person_phone' => '+94 77 123 4567',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Starter']->id,
                'discount_percentage' => 0,
                'activation_key' => Str::random(40),
                'activated_at' => Carbon::now()->subDays(30),
            ],
            [
                'name' => 'Artisan Coffee Roasters',
                'trading_name' => 'Artisan Coffee',
                'registration_number' => 'SB002',
                'email' => 'hello@artisancoffee.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 567 8901',
                'address' => '78 Duplication Road, Colombo 04',
                'website' => 'https://artisancoffee.lk',
                'contact_person' => 'Rohan Silva',
                'contact_person_designation' => 'Founder',
                'contact_person_phone' => '+94 71 234 5678',
                'business_type' => 'cafe',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Professional']->id,
                'discount_percentage' => 10,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(45),
            ],
        ];

        // 2. MULTI-BRANCH FRANCHISES
        $multiBranchOrgs = [
            [
                'name' => 'Golden Spoon Restaurant Group',
                'trading_name' => 'Golden Spoon',
                'registration_number' => 'MB001',
                'email' => 'admin@goldenspoon.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 789 0123',
                'address' => '123 Negombo Road, Colombo 05',
                'website' => 'https://goldenspoon.lk',
                'contact_person' => 'Saman Jayawardena',
                'contact_person_designation' => 'CEO',
                'contact_person_phone' => '+94 77 789 0123',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Enterprise']->id,
                'discount_percentage' => 15,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(90),
            ],
            [
                'name' => 'Island Fresh Food Courts',
                'trading_name' => 'Island Fresh',
                'registration_number' => 'MB002',
                'email' => 'operations@islandfresh.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 81 234 5678',
                'address' => '456 Kandy Road, Peradeniya',
                'website' => 'https://islandfresh.lk',
                'contact_person' => 'Priya Mendis',
                'contact_person_designation' => 'Operations Director',
                'contact_person_phone' => '+94 71 456 7890',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Franchise Master']->id,
                'discount_percentage' => 20,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(120),
            ],
        ];

        // 3. TRIAL ORGANIZATIONS
        $trialOrgs = [
            [
                'name' => 'Startup Bistro',
                'trading_name' => 'Startup Bistro',
                'registration_number' => 'TR001',
                'email' => 'founder@startupbistro.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 345 6789',
                'address' => '89 Reid Avenue, Colombo 07',
                'website' => null,
                'contact_person' => 'Alex Fernando',
                'contact_person_designation' => 'Founder',
                'contact_person_phone' => '+94 76 345 6789',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Professional Trial']->id,
                'discount_percentage' => 0,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(10),
            ],
            [
                'name' => 'Enterprise Test Chain',
                'trading_name' => 'ETC',
                'registration_number' => 'TR002',
                'email' => 'test@enterprisechain.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 456 7890',
                'address' => '234 Union Place, Colombo 02',
                'website' => 'https://enterprisechain.lk',
                'contact_person' => 'Sarah Johnson',
                'contact_person_designation' => 'COO',
                'contact_person_phone' => '+94 77 456 7890',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Enterprise Trial']->id,
                'discount_percentage' => 0,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(5),
            ],
        ];

        // 4. EXPIRED SUBSCRIPTION ORGANIZATIONS
        $expiredOrgs = [
            [
                'name' => 'Sunset Grill',
                'trading_name' => 'Sunset Grill',
                'registration_number' => 'EX001',
                'email' => 'manager@sunsetgrill.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 91 234 5678',
                'address' => '567 Beach Road, Galle',
                'website' => null,
                'contact_person' => 'Kumar Rathnayake',
                'contact_person_designation' => 'Manager',
                'contact_person_phone' => '+94 71 567 8901',
                'business_type' => 'restaurant',
                'status' => 'suspended',
                'is_active' => false,
                'subscription_plan_id' => $plans['Legacy Basic']->id,
                'discount_percentage' => 0,
                'activation_key' => Str::random(40),
                'activated_at' => Carbon::now()->subDays(180),
            ],
        ];

        // 5. SEASONAL BUSINESSES
        $seasonalOrgs = [
            [
                'name' => 'Monsoon Beach Resort',
                'trading_name' => 'Monsoon Resort',
                'registration_number' => 'SE001',
                'email' => 'reservations@monsoonresort.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 47 234 5678',
                'address' => '789 Coastal Highway, Mirissa',
                'website' => 'https://monsoonresort.lk',
                'contact_person' => 'Lakmal Wijesinghe',
                'contact_person_designation' => 'General Manager',
                'contact_person_phone' => '+94 77 789 0123',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Seasonal']->id,
                'discount_percentage' => 5,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(60),
            ],
        ];

        // 6. MOBILE/FOOD TRUCK OPERATIONS
        $mobileOrgs = [
            [
                'name' => 'Colombo Street Food Co.',
                'trading_name' => 'CSF Co.',
                'registration_number' => 'FT001',
                'email' => 'orders@csfco.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 77 123 9876',
                'address' => 'Mobile Operations - Colombo District',
                'website' => 'https://csfco.lk',
                'contact_person' => 'Dinesh Kumarasinghe',
                'contact_person_designation' => 'Owner/Operator',
                'contact_person_phone' => '+94 77 123 9876',
                'business_type' => 'food_truck',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Free']->id,
                'discount_percentage' => 0,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(20),
            ],
        ];

        // 7. INTERNATIONAL ORGANIZATIONS
        $internationalOrgs = [
            [
                'name' => 'Global Flavors International',
                'trading_name' => 'Global Flavors',
                'registration_number' => 'INT001',
                'email' => 'admin@globalflavors.com',
                'password' => Hash::make('password123'),
                'phone' => '+1 555 123 4567',
                'address' => '123 International Plaza, New York, NY',
                'website' => 'https://globalflavors.com',
                'contact_person' => 'Michael Chen',
                'contact_person_designation' => 'Regional Director',
                'contact_person_phone' => '+1 555 987 6543',
                'business_type' => 'restaurant',
                'status' => 'active',
                'is_active' => true,
                'subscription_plan_id' => $plans['Premium Plus']->id,
                'discount_percentage' => 25,
                'activation_key' => null,
                'activated_at' => Carbon::now()->subDays(200),
            ],
        ];

        // 8. PENDING ACTIVATION ORGANIZATIONS
        $pendingOrgs = [
            [
                'name' => 'New Horizon Restaurant',
                'trading_name' => 'New Horizon',
                'registration_number' => 'PA001',
                'email' => 'signup@newhorizon.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 987 6543',
                'address' => '321 Parliament Road, Colombo 07',
                'website' => null,
                'contact_person' => 'Tharindu Jayasuriya',
                'contact_person_designation' => 'Founder',
                'contact_person_phone' => '+94 71 987 6543',
                'business_type' => 'restaurant',
                'status' => 'pending',
                'is_active' => false,
                'subscription_plan_id' => $plans['Enterprise Pro']->id,
                'discount_percentage' => 0,
                'activation_key' => Str::random(40),
                'activated_at' => null,
            ],
        ];

        // Create all organizations and their subscriptions
        $allOrgData = array_merge(
            $singleBranchOrgs,
            $multiBranchOrgs,
            $trialOrgs,
            $expiredOrgs,
            $seasonalOrgs,
            $mobileOrgs,
            $internationalOrgs,
            $pendingOrgs
        );

        $organizations = [];
        foreach ($allOrgData as $orgData) {
            $org = Organization::create($orgData);
            $organizations[] = $org;

            // Create appropriate subscription records
            $this->createSubscriptionForOrganization($org);

            $this->command->info("    ✓ Created organization: {$org->name} ({$org->business_type})");
        }

        $this->command->info("  ✅ Created " . count($organizations) . " organizations covering all onboarding scenarios");
    }

    private function createSubscriptionForOrganization(Organization $org): void
    {
        $plan = $org->subscriptionPlan;
        
        // Determine subscription dates based on organization status
        switch ($org->status) {
            case 'trial':
                $startDate = $org->activated_at ?? Carbon::now();
                $endDate = $startDate->copy()->addDays($plan->trial_period_days);
                $isActive = Carbon::now()->lessThan($endDate);
                break;
                
            case 'suspended':
                $startDate = $org->activated_at ?? Carbon::now()->subDays(180);
                $endDate = $startDate->copy()->addYear();
                $isActive = false;
                break;
                
            case 'pending':
                // No subscription until activated
                return;
                
            case 'seasonal':
                $startDate = $org->activated_at ?? Carbon::now()->subDays(60);
                $endDate = $startDate->copy()->addMonths(6); // Seasonal validity
                $isActive = true;
                break;
                
            default: // active
                $startDate = $org->activated_at ?? Carbon::now()->subDays(30);
                $endDate = $startDate->copy()->addYear();
                $isActive = true;
                break;
        }

        Subscription::create([
            'organization_id' => $org->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $isActive ? 'active' : 'suspended',
            'is_active' => $isActive,
            'is_trial' => $plan->is_trial,
            'activated_at' => $org->activated_at,
        ]);
    }
}
