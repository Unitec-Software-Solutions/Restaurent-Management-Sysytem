<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\Admin;
use App\Services\OrganizationAutomationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminOrganizationSeeder extends Seeder
{
    protected $organizationAutomationService;

    public function __construct(OrganizationAutomationService $organizationAutomationService)
    {
        $this->organizationAutomationService = $organizationAutomationService;
    }

    /**
     * Seed super admin and organizations with head offices
     */
    public function run(): void
    {
        $this->command->info('👑 Creating Super Admin & Organizations...');
        
        // Create super admin first
        $this->createSuperAdmin();
        
        // Get subscription plans
        $subscriptionPlans = SubscriptionPlan::all()->keyBy('name');
        
        // Create organizations with different plan types
        $this->createOrganizations($subscriptionPlans);
        
        $this->command->info('✅ Super Admin & Organizations created successfully');
    }

    private function createSuperAdmin(): void
    {
        // Create super admin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Administrator',
            'guard_name' => 'admin'
        ]);

        // Create super admin user
        $superAdmin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@restaurant-system.com',
            'password' => Hash::make('superadmin123'),
            'phone' => '+94 11 000 0000',
            'job_title' => 'System Administrator',
            'is_super_admin' => true,
            'is_active' => true,
            'status' => 'active',
            'email_verified_at' => now()
        ]);

        // Assign super admin role
        $superAdmin->assignRole($superAdminRole);

        $this->command->info("    ✓ Super Admin created: {$superAdmin->email}");
    }

    private function createOrganizations($subscriptionPlans): void
    {
        $organizationsData = [
            // Premium Restaurant Chain
            [
                'name' => 'Golden Spoon Restaurant Group',
                'trading_name' => 'Golden Spoon',
                'registration_number' => 'REG001',
                'email' => 'admin@goldenspoon.lk',
                'phone' => '+94 11 234 5678',
                'address' => '123 Main Street, Colombo 03, Sri Lanka',
                'contact_person' => 'Rajesh Perera',
                'contact_person_designation' => 'Managing Director',
                'contact_person_phone' => '+94 77 123 4567',
                'business_type' => 'restaurant_chain',
                'subscription_plan' => 'Premium',
                'discount_percentage' => 10
            ],
            
            // Enterprise Level Chain
            [
                'name' => 'Ceylon Cuisine International',
                'trading_name' => 'Ceylon Cuisine',
                'registration_number' => 'REG002',
                'email' => 'admin@ceyloncuisine.com',
                'phone' => '+94 11 345 6789',
                'address' => '456 Galle Road, Colombo 04, Sri Lanka',
                'contact_person' => 'Priya Silva',
                'contact_person_designation' => 'CEO',
                'contact_person_phone' => '+94 77 234 5678',
                'business_type' => 'international_chain',
                'subscription_plan' => 'Enterprise',
                'discount_percentage' => 15
            ],

            // Basic Single Location
            [
                'name' => 'Mama\'s Kitchen',
                'trading_name' => 'Mama\'s Kitchen',
                'registration_number' => 'REG003',
                'email' => 'admin@mamaskitchen.lk',
                'phone' => '+94 11 456 7890',
                'address' => '789 Kandy Road, Colombo 07, Sri Lanka',
                'contact_person' => 'Saman Fernando',
                'contact_person_designation' => 'Owner',
                'contact_person_phone' => '+94 77 345 6789',
                'business_type' => 'family_restaurant',
                'subscription_plan' => 'Basic',
                'discount_percentage' => 0
            ],

            // Trial Organization
            [
                'name' => 'Fresh Start Cafe',
                'trading_name' => 'Fresh Start',
                'registration_number' => 'REG004',
                'email' => 'admin@freshstart.lk',
                'phone' => '+94 11 567 8901',
                'address' => '321 Negombo Road, Colombo 15, Sri Lanka',
                'contact_person' => 'Nimal Jayasinghe',
                'contact_person_designation' => 'Manager',
                'contact_person_phone' => '+94 77 456 7890',
                'business_type' => 'cafe',
                'subscription_plan' => 'Free Trial',
                'discount_percentage' => 0
            ],

            // Premium Fast Food Chain
            [
                'name' => 'Spicy Bites Fast Food',
                'trading_name' => 'Spicy Bites',
                'registration_number' => 'REG005',
                'email' => 'admin@spicybites.lk',
                'phone' => '+94 11 678 9012',
                'address' => '654 High Level Road, Nugegoda, Sri Lanka',
                'contact_person' => 'Kamal Wickramasinghe',
                'contact_person_designation' => 'Franchise Owner',
                'contact_person_phone' => '+94 77 567 8901',
                'business_type' => 'fast_food',
                'subscription_plan' => 'Premium',
                'discount_percentage' => 5
            ]
        ];

        foreach ($organizationsData as $orgData) {
            $this->createOrganizationWithAutomation($orgData, $subscriptionPlans);
        }
    }

    private function createOrganizationWithAutomation(array $orgData, $subscriptionPlans): void
    {
        // Get subscription plan
        $subscriptionPlan = $subscriptionPlans->get($orgData['subscription_plan']);
        
        if (!$subscriptionPlan) {
            $this->command->warn("    ⚠️ Subscription plan '{$orgData['subscription_plan']}' not found for {$orgData['name']}");
            return;
        }

        // Prepare organization creation data
        $organizationData = [
            'name' => $orgData['name'],
            'trading_name' => $orgData['trading_name'],
            'registration_number' => $orgData['registration_number'],
            'email' => $orgData['email'],
            'password' => Hash::make('organization123'),
            'phone' => $orgData['phone'],
            'address' => $orgData['address'],
            'contact_person' => $orgData['contact_person'],
            'contact_person_designation' => $orgData['contact_person_designation'],
            'contact_person_phone' => $orgData['contact_person_phone'],
            'business_type' => $orgData['business_type'],
            'subscription_plan_id' => $subscriptionPlan->id,
            'discount_percentage' => $orgData['discount_percentage'],
            'is_active' => true,
            'activated_at' => now()->subDays(rand(1, 30)),
            'activation_key' => null // Already activated
        ];

        try {
            // Use automation service to create organization with head office
            $organization = $this->organizationAutomationService->setupNewOrganization($organizationData);
            
            $this->command->info("    ✓ Organization: {$organization->name} ({$orgData['subscription_plan']} plan)");
            
            // Display created branches
            foreach ($organization->branches as $branch) {
                $this->command->info("      → Branch: {$branch->name}" . ($branch->is_head_office ? ' (Head Office)' : ''));
            }
            
            // Display created admins
            foreach ($organization->admins as $admin) {
                $this->command->info("      → Admin: {$admin->name} ({$admin->email})");
            }
            
        } catch (\Exception $e) {
            $this->command->error("    ❌ Failed to create organization {$orgData['name']}: " . $e->getMessage());
        }
    }
}
