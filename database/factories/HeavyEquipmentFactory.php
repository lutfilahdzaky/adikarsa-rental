<?php

namespace Database\Factories;

use App\Models\HeavyEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeavyEquipment>
 */
class HeavyEquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' ' . fake()->word(),
            'description' => fake()->sentence(),
            'daily_rate' => fake()->numberBetween(500000, 5000000),
            'photo' => fake()->imageUrl(640, 480, 'transport', true),
        ];
    }
}
