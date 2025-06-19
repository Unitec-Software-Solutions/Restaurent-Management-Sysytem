<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition()
    {
        return [
            'organization_id' => Organization::factory(),
            'supplier_id' => $this->faker->unique()->bothify('SUP-######'),
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'has_vat_registration' => $this->faker->boolean(70),
            'vat_registration_no' => $this->faker->optional(0.7)->bothify('VAT#########'),
            'is_active' => true,
            'is_inactive' => false,
        ];
    }
}
