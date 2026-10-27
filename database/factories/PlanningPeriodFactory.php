<?php

namespace Database\Factories;

use App\Models\PlanningPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanningPeriod>
 */
class PlanningPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->unique()->numberBetween(2030, 2100),
            'starts_at' => fn (array $attributes) => $attributes['year'].'-01-01',
            'ends_at' => fn (array $attributes) => $attributes['year'].'-12-31',
            'is_active' => false,
        ];
    }
}
