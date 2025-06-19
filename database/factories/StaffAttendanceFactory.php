<?php

namespace Database\Factories;

use App\Models\StaffAttendance;
use App\Models\StaffProfile;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffAttendanceFactory extends Factory
{
    protected $model = StaffAttendance::class;    public function definition()
    {
        $checkIn = $this->faker->dateTimeBetween('-1 week', 'now');
        $checkOut = $this->faker->optional(0.8)->dateTimeBetween($checkIn, 'now');
        
        return [
            'staff_profile_id' => StaffProfile::factory(),
            'branch_id' => Branch::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $this->faker->randomElement(['present', 'absent', 'late', 'half-day', 'overtime']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function present()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'present',
                'check_in' => $this->faker->dateTimeBetween('-8 hours', '-7 hours'),
                'check_out' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            ];
        });
    }

    public function absent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'absent',
                'check_in' => null,
                'check_out' => null,
            ];
        });
    }

    public function late()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'late',
                'check_in' => $this->faker->dateTimeBetween('-6 hours', '-5 hours'),
                'notes' => 'Arrived late',
            ];
        });
    }
}
