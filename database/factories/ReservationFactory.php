<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'office_id' => Office::inRandomOrder()->first()->id,
            'approval_status' => 1,
            'price' => $this->faker->numberBetween(10000,20000),
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ];
    }
}
