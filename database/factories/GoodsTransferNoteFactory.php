<?php

namespace Database\Factories;

use App\Models\GoodsTransferNote;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsTransferNoteFactory extends Factory
{
    protected $model = GoodsTransferNote::class;    public function definition()
    {
        // Define logical status combinations for GTN workflow
        $statusCombinations = [
            [
                'status' => 'pending',
                'origin_status' => 'draft', 
                'receiver_status' => 'pending'
            ],
            [
                'status' => 'approved',
                'origin_status' => 'confirmed',
                'receiver_status' => 'pending'
            ],
            [
                'status' => 'approved', 
                'origin_status' => 'in_delivery',
                'receiver_status' => 'pending'
            ],
            [
                'status' => 'completed',
                'origin_status' => 'delivered',
                'receiver_status' => 'accepted'
            ]
        ];
        
        $selectedStatus = $this->faker->randomElement($statusCombinations);
        
        return [
            'gtn_number' => $this->faker->unique()->numerify('GTN####'),
            'from_branch_id' => Branch::factory(),
            'to_branch_id' => Branch::factory(),            'created_by' => Employee::factory(),
            'approved_by' => Employee::factory(),
            'organization_id' => Organization::factory(),
            'transfer_date' => $this->faker->date(),
            'status' => $selectedStatus['status'],
            'origin_status' => $selectedStatus['origin_status'],
            'receiver_status' => $selectedStatus['receiver_status'],
            'confirmed_at' => $this->faker->optional()->dateTime(),
            'delivered_at' => $this->faker->optional()->dateTime(),
            'received_at' => $this->faker->optional()->dateTime(),
            'verified_at' => $this->faker->optional()->dateTime(),
            'accepted_at' => $this->faker->optional()->dateTime(),
            'rejection_reason' => $this->faker->optional()->sentence(),            'rejected_by' => Employee::factory(),
            'rejected_at' => $this->faker->optional()->dateTime(),
            'verified_by' => Employee::factory(),
            'received_by' => Employee::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
