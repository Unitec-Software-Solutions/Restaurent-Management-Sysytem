<?php

namespace Database\Factories;

use App\Models\StaffShift;
use App\Models\StaffProfile;
use App\Models\Shift;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffShiftFactory extends Factory
{
    protected $model = StaffShift::class;

    public function definition()
    {
        $clockIn = $this->faker->optional(0.8)->dateTimeBetween('-8 hours', '-1 hour');
        $clockOut = $clockIn ? $this->faker->optional(0.6)->dateTimeBetween($clockIn, 'now') : null;
        
        return [
            'staff_profile_id' => StaffProfile::factory(),
            'shift_id' => Shift::factory(),
            'branch_id' => Branch::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'is_training_mode' => $this->faker->boolean(20),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function clockedIn()
    {
        return $this->state(function (array $attributes) {
            return [
                'clock_in' => $this->faker->dateTimeBetween('-8 hours', '-1 hour'),
                'clock_out' => null,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            $clockIn = $this->faker->dateTimeBetween('-10 hours', '-8 hours');
            return [
                'clock_in' => $clockIn,
                'clock_out' => $this->faker->dateTimeBetween($clockIn, '-1 hour'),
            ];
        });
    }

    public function training()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_training_mode' => true,
                'notes' => 'Training shift',
            ];
        });
    }
}
