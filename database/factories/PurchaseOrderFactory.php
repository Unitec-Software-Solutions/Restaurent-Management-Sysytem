<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Supplier;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition()
    {
        return [
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
            'supplier_id' => $this->faker->randomNumber(),
            'user_id' => Admin::factory(),
            'po_number' => $this->faker->unique()->numerify('PO####'),
            'order_date' => $this->faker->date(),
            'expected_delivery_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['Pending','Approved','Received','Partial','Cancelled']),
            'total_amount' => $this->faker->randomFloat(2, 10, 10000),
            'paid_amount' => $this->faker->randomFloat(2, 0, 10000),
            'payment_method' => $this->faker->randomElement(['cash','card','bank']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
