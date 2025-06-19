<?php

namespace Database\Factories;

use App\Models\GoodsTransferNote;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsTransferNoteFactory extends Factory
{
    protected $model = GoodsTransferNote::class;

    public function definition()
    {
        return [
            'gtn_number' => $this->faker->unique()->numerify('GTN####'),
            'from_branch_id' => Branch::factory(),
            'to_branch_id' => Branch::factory(),
            'created_by' => Admin::factory(),
            'approved_by' => Admin::factory(),
            'organization_id' => Organization::factory(),
            'transfer_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending','approved','rejected','completed']),
            'origin_status' => $this->faker->randomElement(['pending','approved','rejected','completed']),
            'receiver_status' => $this->faker->randomElement(['pending','approved','rejected','completed']),
            'confirmed_at' => $this->faker->optional()->dateTime(),
            'delivered_at' => $this->faker->optional()->dateTime(),
            'received_at' => $this->faker->optional()->dateTime(),
            'verified_at' => $this->faker->optional()->dateTime(),
            'accepted_at' => $this->faker->optional()->dateTime(),
            'rejection_reason' => $this->faker->optional()->sentence(),
            'rejected_by' => Admin::factory(),
            'rejected_at' => $this->faker->optional()->dateTime(),
            'verified_by' => Admin::factory(),
            'received_by' => Admin::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
