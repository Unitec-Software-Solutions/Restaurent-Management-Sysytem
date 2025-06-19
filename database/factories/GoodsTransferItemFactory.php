<?php

namespace Database\Factories;

use App\Models\GoodsTransferItem;
use App\Models\GoodsTransferNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsTransferItemFactory extends Factory
{
    protected $model = GoodsTransferItem::class;

    public function definition()
    {
        return [
            'gtn_id' => GoodsTransferNote::factory(),
            'item_id' => $this->faker->randomNumber(),
            'item_code' => $this->faker->bothify('ITEM-####'),
            'item_name' => $this->faker->word(),
            'batch_no' => $this->faker->bothify('BATCH-####'),
            'expiry_date' => $this->faker->optional()->date(),
            'transfer_quantity' => $this->faker->randomFloat(2, 1, 100),
            'received_quantity' => $this->faker->randomFloat(2, 1, 100),
            'damaged_quantity' => $this->faker->randomFloat(2, 0, 10),
            'quantity_accepted' => $this->faker->randomFloat(2, 1, 100),
            'quantity_rejected' => $this->faker->randomFloat(2, 0, 10),
            'transfer_price' => $this->faker->randomFloat(2, 1, 100),
            'line_total' => $this->faker->randomFloat(2, 1, 1000),
            'notes' => $this->faker->optional()->sentence(),
            'item_rejection_reason' => $this->faker->optional()->sentence(),
            'item_status' => $this->faker->randomElement(['pending','accepted','rejected']),
            'quality_notes' => $this->faker->optional()->sentence(),
            'inspected_by' => $this->faker->name(),
            'inspected_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
