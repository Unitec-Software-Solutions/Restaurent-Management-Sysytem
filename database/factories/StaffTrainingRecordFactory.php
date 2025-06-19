<?php

namespace Database\Factories;

use App\Models\StaffTrainingRecord;
use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffTrainingRecordFactory extends Factory
{
    protected $model = StaffTrainingRecord::class;

    public function definition()
    {
        return [
            'staff_profile_id' => StaffProfile::factory(),
            'trainer_id' => StaffProfile::factory(),
            'training_type' => $this->faker->randomElement(['orientation', 'safety', 'customer_service', 'technical', 'management']),
            'description' => $this->faker->sentence(),
            'training_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_completed' => $this->faker->boolean(80),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_completed' => true,
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_completed' => false,
                'training_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            ];
        });
    }

    public function safety()
    {
        return $this->state(function (array $attributes) {
            return [
                'training_type' => 'safety',
                'description' => 'Safety procedures and protocols training',
            ];
        });
    }
}
