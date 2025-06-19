<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition()
    {
        $startTime = $this->faker->time('H:i:s', '22:00:00');
        $endTime = $this->faker->time('H:i:s', '23:59:59');
        
        return [
            'name' => $this->faker->randomElement(['Morning Shift', 'Afternoon Shift', 'Evening Shift', 'Night Shift']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function morning()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
            ];
        });
    }

    public function afternoon()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Afternoon Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
            ];
        });
    }

    public function night()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
            ];
        });
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }
}
