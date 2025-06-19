<?php

namespace Database\Factories;

use App\Models\SupplierPaymentMaster;
use App\Models\Supplier;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierPaymentMasterFactory extends Factory
{
    protected $model = SupplierPaymentMaster::class;    public function definition()
    {
        return [
            'supplier_id' => Supplier::factory(),
            'organization_id' => Organization::factory(),
            'branch_id' => Branch::factory(),
            'payment_number' => $this->faker->unique()->bothify('PAY-####'),
            'payment_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'allocated_amount' => $this->faker->randomFloat(2, 0, 5000),
            'currency' => 'LKR',
            'payment_status' => $this->faker->randomElement(['draft','approved','partial','completed','pending','overdue','reversed']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
