<?php

namespace Database\Factories;

use App\Models\GrnItem;
use App\Models\GrnMaster;
use App\Models\ItemMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrnItemFactory extends Factory
{
    protected $model = GrnItem::class;

    public function definition()
    {
        return [
            'grn_id' => GrnMaster::factory(),
            'item_id' => ItemMaster::factory(),
            'item_code' => $this->faker->bothify('ITEM-####'),
            'item_name' => $this->faker->word(),
            'batch_no' => $this->faker->bothify('BATCH-####'),
            'ordered_quantity' => $this->faker->randomFloat(2, 1, 100),
            'received_quantity' => $this->faker->randomFloat(2, 1, 100),
            'accepted_quantity' => $this->faker->randomFloat(2, 1, 100),            'free_received_quantity' => $this->faker->randomFloat(2, 0, 10),
            'rejected_quantity' => $this->faker->randomFloat(2, 0, 10),
            'buying_price' => $this->faker->randomFloat(2, 1, 100),
            'line_total' => $this->faker->randomFloat(2, 1, 1000),
            'manufacturing_date' => $this->faker->optional()->date(),
            'expiry_date' => $this->faker->optional()->date(),
            'rejection_reason' => $this->faker->optional()->sentence(),
            'discount_received' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
