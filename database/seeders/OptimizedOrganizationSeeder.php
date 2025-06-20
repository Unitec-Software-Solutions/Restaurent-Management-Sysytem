<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OptimizedOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing organizations for clean test data
        Organization::truncate();
        
        // Get subscription plans
        $basicPlan = SubscriptionPlan::where('name', 'Basic')->first();
        $proPlan = SubscriptionPlan::where('name', 'Pro')->first();
        $legacyPlan = SubscriptionPlan::where('name', 'Legacy')->first();

        $organizations = [
            [
                'name' => 'Spice Garden Restaurant',
                'trading_name' => 'Spice Garden',
                'registration_number' => 'REG001',
                'email' => 'admin@spicegarden.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 11 234 5678',
                'address' => '123 Galle Road, Colombo 03',
                'contact_person' => 'Kumara Silva',
                'contact_person_designation' => 'General Manager',
                'contact_person_phone' => '+94 77 123 4567',
                'is_active' => true,
                'subscription_plan_id' => $proPlan?->id,
                'discount_percentage' => 0,
                'activation_key' => Str::random(40),
                'business_type' => 'restaurant',
                'status' => 'active',
            ],
            [
                'name' => 'Ocean View Cafe',
                'trading_name' => 'Ocean View',
                'registration_number' => 'REG002',
                'email' => 'admin@oceanview.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 31 567 8901',
                'address' => '456 Marine Drive, Galle',
                'contact_person' => 'Nishani Fernando',
                'contact_person_designation' => 'Owner',
                'contact_person_phone' => '+94 77 234 5678',
                'is_active' => true,
                'subscription_plan_id' => $basicPlan?->id,
                'discount_percentage' => 5,
                'activation_key' => Str::random(40),
                'business_type' => 'restaurant',
                'status' => 'active',
            ],
            [
                'name' => 'Mountain Peak Restaurant',
                'trading_name' => 'Mountain Peak',
                'registration_number' => 'REG003',
                'email' => 'admin@mountainpeak.lk',
                'password' => Hash::make('password123'),
                'phone' => '+94 81 345 6789',
                'address' => '789 Hill Street, Kandy',
                'contact_person' => 'Rohan Perera',
                'contact_person_designation' => 'Restaurant Manager',
                'contact_person_phone' => '+94 77 345 6789',
                'is_active' => true,
                'subscription_plan_id' => $legacyPlan?->id,
                'discount_percentage' => 10,
                'activation_key' => Str::random(40),
                'business_type' => 'restaurant',
                'status' => 'active',
            ],
        ];

        foreach ($organizations as $orgData) {
            $organization = Organization::create($orgData);
            
            // Create active subscription for each organization
            if ($organization->subscription_plan_id) {
                \App\Models\Subscription::create([
                    'organization_id' => $organization->id,
                    'plan_id' => $organization->subscription_plan_id,
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'status' => 'active',
                    'is_active' => true,
                    'is_trial' => false,
                ]);
            }
            
            $this->command->info("  ✅ Created organization: {$organization->name}");
        }

        $this->command->info("  Total Organizations: " . Organization::count());
        $this->command->info("  ✅ Organizations seeded successfully with realistic data.");
    }
}
