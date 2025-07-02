<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MenuCategory;
use App\Models\Branch;
use App\Models\Organization;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    protected $model = MenuCategory::class;

    /**
     * Define the model's default state for Laravel + PostgreSQL + Tailwind CSS stack.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random branch or create one if none exist
        $branch = Branch::inRandomOrder()->first() ?? Branch::factory()->create();
        
        return [
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'name' => $this->faker->words(2, true),
            'unicode_name' => function (array $attributes) {
                return $attributes['name'];
            },
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'display_order' => function (array $attributes) {
                return $attributes['sort_order'];
            },
            'is_active' => true,
            'is_inactive' => false,
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'image_url' => $this->faker->optional()->imageUrl(400, 300, 'food'),
            'settings' => [
                'show_in_menu' => true,
                'allow_customization' => $this->faker->boolean(30),
                'require_age_verification' => false,
                'tax_applicable' => true,
                'service_charge_applicable' => true
            ],
            'availability_schedule' => [
                'monday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'tuesday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'wednesday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'thursday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'friday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'saturday' => ['open' => '00:00', 'close' => '23:59', 'available' => true],
                'sunday' => ['open' => '00:00', 'close' => '23:59', 'available' => true]
            ],
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create categories for a specific branch
     */
    public function forBranch(Branch $branch): static
    {
        return $this->state(function (array $attributes) use ($branch) {
            return [
                'branch_id' => $branch->id,
                'organization_id' => $branch->organization_id,
            ];
        });
    }

    /**
     * Create featured category
     */
    public function featured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_featured' => true,
            ];
        });
    }

    /**
     * Create inactive category
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
                'is_inactive' => true,
            ];
        });
    }

    /**
     * Create category with specific name for PostgreSQL compatibility
     */
    public function withName(string $name): static
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => $name,
                'unicode_name' => $name,
            ];
        });
    }
}
