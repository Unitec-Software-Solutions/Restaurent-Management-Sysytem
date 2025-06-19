<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Supplier;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition()
    {
        $currentStock = $this->faker->randomFloat(2, 0, 1000);
        $minimumStock = $this->faker->randomFloat(2, 1, 50);
        
        return [
            'item_code' => $this->faker->unique()->bothify('INV-####'),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'category_id' => ItemCategory::factory(),
            'unit_of_measurement' => $this->faker->randomElement(['kg', 'g', 'l', 'ml', 'pcs', 'box']),
            'current_stock' => $currentStock,
            'minimum_stock' => $minimumStock,
            'maximum_stock' => $this->faker->randomFloat(2, $minimumStock + 50, 2000),
            'unit_cost' => $this->faker->randomFloat(2, 1, 100),
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'expiry_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+2 years'),
            'supplier_id' => Supplier::factory(),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function lowStock()
    {
        return $this->state(function (array $attributes) {
            return [
                'current_stock' => $this->faker->randomFloat(2, 0, $attributes['minimum_stock']),
            ];
        });
    }

    public function expiringSoon()
    {
        return $this->state(function (array $attributes) {
            return [
                'expiry_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            ];
        });
    }
}
