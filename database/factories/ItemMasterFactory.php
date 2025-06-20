<?php

namespace Database\Factories;

use App\Models\ItemMaster;
use App\Models\ItemCategory;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemMasterFactory extends Factory
{
    protected $model = ItemMaster::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'unicode_name' => $this->faker->word(),
            'item_category_id' => ItemCategory::factory(),
            'item_code' => $this->faker->bothify('ITEM-####'),
            'unit_of_measurement' => $this->faker->randomElement(['kg','g','l','ml','pcs']),
            'reorder_level' => $this->faker->numberBetween(1, 50),
            'is_perishable' => $this->faker->boolean(),
            'shelf_life_in_days' => $this->faker->numberBetween(1, 365),
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
            'buying_price' => $this->faker->randomFloat(2, 1, 100),
            'selling_price' => $this->faker->randomFloat(2, 1, 200),
            'is_menu_item' => $this->faker->boolean(),
            'additional_notes' => $this->faker->optional()->sentence(),
            'description' => $this->faker->sentence(),
            'attributes' => $this->faker->randomElements(['spicy','vegan','gluten-free','organic'], $this->faker->numberBetween(0,2)),
        ];
    }
}
