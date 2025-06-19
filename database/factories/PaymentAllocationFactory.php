<?php

namespace Database\Factories;

use App\Models\PaymentAllocation;
use App\Models\GrnMaster;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentAllocationFactory extends Factory
{
    protected $model = PaymentAllocation::class;

    public function definition()
    {
        return [
            'payment_id' => $this->faker->randomNumber(),
            'grn_id' => GrnMaster::factory(),
            'po_id' => PurchaseOrder::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'allocated_at' => $this->faker->optional()->dateTime(),
            'allocated_by' => $this->faker->randomNumber(),
        ];
    }
}
