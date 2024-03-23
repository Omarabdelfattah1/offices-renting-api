<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'address_line1' => $this->faker->address,
            'approval_status' => 1,
            'hidden' => false,
            'price_per_day' => $this->faker->randomFloat(2,1000,2000),
            'monthly_discount' => rand(1,90),
        ];
    }

    public function configure(){
        return $this->afterCreating(function (Office $office) {
            $feaured = $office->images()->create([
                'path' => 'image.png'
            ]);
            $office->update([
                'featured_image_id' => $feaured->id,
            ]);
        });
    }
}
