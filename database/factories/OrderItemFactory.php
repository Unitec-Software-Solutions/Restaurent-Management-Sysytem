<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ItemMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;    public function definition()
    {
        // Get a random existing ItemMaster ID, or create one if none exist
        $itemMasterId = \App\Models\ItemMaster::inRandomOrder()->first()?->id ?? \App\Models\ItemMaster::factory()->create()->id;
        
        return [
            'order_id' => Order::factory(),
            'menu_item_id' => $itemMasterId,
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->randomFloat(2, 1, 100),
            'total_price' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['unit_price'];
            },
            'inventory_item_id' => $itemMasterId,
        ];
    }
}
