<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $branch = Branch::inRandomOrder()->first();

        return [
            'branch_id' => $branch ? $branch->id : null,
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'date' => Carbon::now()->addDays(rand(1, 30))->format('Y-m-d'),
            'start_time' => Carbon::now()->addDays(rand(1, 30))->format('H:i:s'),
            'end_time' => Carbon::now()->addDays(rand(1, 30))->addHours(2)->format('H:i:s'),
            'number_of_people' => rand(1, 10),
            'status' => 'confirmed',
        ];
    }
}
