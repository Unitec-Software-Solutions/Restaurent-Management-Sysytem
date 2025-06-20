<?php

namespace Database\Factories;

use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\ItemMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PurchaseOrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $quantity = $this->faker->randomFloat(3, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 1, 1000);
        
        return [
            'po_id' => PurchaseOrder::factory(),
            'item_id' => ItemMaster::factory(),
            'batch_no' => $this->faker->optional()->bothify('BATCH-####'),
            'quantity' => $quantity,
            'buying_price' => $unitPrice,
            'line_total' => $quantity * $unitPrice,
            'po_status' => $this->faker->randomElement(['Pending','Approved','Received','Partial','Cancelled']),
        ];
    }
}