<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'reservation_id' => Reservation::factory(),
            'branch_id' => Branch::factory(),
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'order_type' => $this->faker->randomElement([
                Order::TYPE_TAKEAWAY_IN_CALL,
                Order::TYPE_TAKEAWAY_ONLINE,
                Order::TYPE_TAKEAWAY_WALKIN_SCHEDULED,
                Order::TYPE_TAKEAWAY_WALKIN_DEMAND,
                Order::TYPE_DINEIN_ONLINE,
                Order::TYPE_DINEIN_INCALL,
                Order::TYPE_DINEIN_WALKIN_SCHEDULED,
                Order::TYPE_DINEIN_WALKIN_DEMAND,
            ]),
            'status' => $this->faker->randomElement([
                Order::STATUS_ACTIVE,
                Order::STATUS_SUBMITTED,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]),            'total' => $this->faker->randomFloat(2, 10, 500),
            'notes' => $this->faker->sentence(),
        ];
    }
}
