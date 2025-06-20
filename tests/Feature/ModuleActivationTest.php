<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModuleActivationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function premium_features_blocked_on_basic_plan()
    {
        // Create basic plan organization
        $basicPlan = SubscriptionPlan::create([
            'name' => 'Basic',
            'features' => ['basic_ordering', 'cash_payments'],
            'price' => 0,
            'currency' => 'LKR'
        ]);
        
        $org = Organization::create([
            'name' => 'Test Org',
            'email' => 'test@example.com',
            'subscription_plan_id' => $basicPlan->id
        ]);
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id
        ]);
        
        $this->actingAs($user);
        
        // Premium feature should be blocked
        $this->assertFalse($org->hasFeature('split_billing'));
        $this->assertFalse($org->hasFeature('advanced_analytics'));
    }

    /** @test */
    public function premium_features_allowed_on_pro_plan()
    {
        // Create pro plan organization
        $proPlan = SubscriptionPlan::create([
            'name' => 'Pro',
            'features' => ['basic_ordering', 'split_billing', 'advanced_analytics'],
            'price' => 5000,
            'currency' => 'LKR'
        ]);
        
        $org = Organization::create([
            'name' => 'Test Pro Org',
            'email' => 'pro@example.com',
            'subscription_plan_id' => $proPlan->id
        ]);
        
        $user = User::create([
            'name' => 'Pro User',
            'email' => 'prouser@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id
        ]);
        
        $this->actingAs($user);
        
        // Premium features should be allowed
        $this->assertTrue($org->hasFeature('split_billing'));
        $this->assertTrue($org->hasFeature('advanced_analytics'));
    }

    /** @test */
    public function subscription_limits_enforced()
    {
        $basicPlan = SubscriptionPlan::create([
            'name' => 'Basic',
            'max_branches' => 2,
            'max_employees' => 10,
            'price' => 0,
            'currency' => 'LKR'
        ]);
        
        $org = Organization::create([
            'name' => 'Limited Org',
            'email' => 'limited@example.com',
            'subscription_plan_id' => $basicPlan->id
        ]);
        
        // Create 2 branches (at limit)
        $org->branches()->createMany([
            ['name' => 'Branch 1', 'address' => 'Address 1', 'phone' => '123', 'is_active' => true],
            ['name' => 'Branch 2', 'address' => 'Address 2', 'phone' => '456', 'is_active' => true]
        ]);
        
        // Should not be able to add more branches
        $this->assertFalse($org->canAddBranches());
        
        // Create 10 employees (at limit)
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => "Employee {$i}",
                'email' => "employee{$i}@example.com",
                'password' => bcrypt('password'),
                'organization_id' => $org->id
            ]);
        }
        
        // Should not be able to add more employees
        $this->assertFalse($org->canAddEmployees());
    }
}
