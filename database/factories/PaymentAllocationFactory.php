<?php

namespace Database\Factories;

use App\Models\PaymentAllocation;
use App\Models\GrnMaster;
use App\Models\PurchaseOrder;
use App\Models\SupplierPaymentMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentAllocationFactory extends Factory
{
    protected $model = PaymentAllocation::class;    public function definition()
    {
        return [
            'payment_id' => SupplierPaymentMaster::factory(),
            'grn_id' => GrnMaster::factory(),
            'po_id' => PurchaseOrder::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'allocated_at' => $this->faker->dateTime(),
            'allocated_by' => $this->faker->randomNumber(),
        ];
    }
}
