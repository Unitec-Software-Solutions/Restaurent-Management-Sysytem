<?php

namespace Database\Factories;

use App\Models\GrnMaster;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Supplier;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrnMasterFactory extends Factory
{
    protected $model = GrnMaster::class;

    public function definition()
    {
        return [
            'grn_number' => $this->faker->unique()->numerify('GRN####'),
            'po_id' => $this->faker->randomNumber(),
            'branch_id' => Branch::factory(),
            'organization_id' => Organization::factory(),
            'supplier_id' => $this->faker->randomNumber(),
            'received_by_user_id' => Admin::factory(),
            'received_date' => $this->faker->date(),
            'delivery_note_number' => $this->faker->bothify('DN-####'),
            'invoice_number' => $this->faker->bothify('INV-####'),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['pending','approved','rejected','completed']),
            'is_active' => $this->faker->boolean(),
            'created_by' => Admin::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10, 10000),
            'grand_discount' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
