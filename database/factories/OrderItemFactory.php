<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\ItemMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 100);
        $quantity = $this->faker->numberBetween(1, 5);
        
        return [
            'order_id' => Order::factory(),
            'menu_item_id' => MenuItem::factory(),
            'inventory_item_id' => ItemMaster::factory(),
            'item_name' => $this->faker->words(2, true),
            'item_description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'subtotal' => $unitPrice * $quantity,
            'customizations' => null,
            'special_instructions' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'preparing', 'ready', 'served']),
            'prepared_at' => $this->faker->optional()->dateTimeBetween('-1 hour', 'now'),
            'served_at' => $this->faker->optional()->dateTimeBetween('-30 minutes', 'now'),
        ];
    }

    /**
     * Configure the factory with specific menu item and pricing
     */
    public function forMenuItem(MenuItem $menuItem): static
    {
        return $this->state(function (array $attributes) use ($menuItem) {
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 3);
            
            return [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'item_description' => $menuItem->description,
                'unit_price' => $menuItem->price,
                'total_price' => $menuItem->price * $quantity,
                'subtotal' => $menuItem->price * $quantity,
            ];
        });
    }
}
