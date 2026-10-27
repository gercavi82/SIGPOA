<?php

namespace Database\Factories;

use App\Enums\BudgetCeilingStatus;
use App\Models\BudgetCeiling;
use App\Models\PlanningPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetCeiling>
 */
class BudgetCeilingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planning_period_id' => PlanningPeriod::factory(), 'source' => fake()->unique()->word(), 'current_expenditure' => 60, 'investment_expenditure' => 40, 'total_amount' => 100, 'methodology' => 'Metodología validada', 'status' => BudgetCeilingStatus::Draft, 'created_by' => User::factory(), 'version' => 1,
        ];
    }
}
