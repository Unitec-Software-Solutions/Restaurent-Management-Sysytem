<?php

namespace Database\Factories;

use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
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
            'purchase_order_id' => PurchaseOrder::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => $quantity,
            'received_quantity' => $this->faker->randomFloat(3, 0, $quantity),
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'is_active' => true,
        ];
    }
}