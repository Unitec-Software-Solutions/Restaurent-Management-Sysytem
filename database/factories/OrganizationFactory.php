<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition()
    {
        // Make subscription plan optional since the table may not exist
        $plan = null;
        try {
            $plan = SubscriptionPlan::inRandomOrder()->first();
        } catch (\Exception $e) {
            // Subscription plans table doesn't exist, use null
        }
        
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'contact_person_designation' => $this->faker->jobTitle(),
            'contact_person_phone' => $this->faker->phoneNumber(),
            'is_active' => $this->faker->boolean(),
            'subscription_plan_id' => $plan ? $plan->id : null, // nullable if no plans
            'discount_percentage' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
