<?php

namespace Database\Factories;

use App\Models\BudgetCertification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetCertificationFactory extends Factory
{
    protected $model = BudgetCertification::class;

    public function definition(): array
    {
        return [
            'certification_number' => 'CP-'.fake()->unique()->numberBetween(1000, 9999),
            'certification_date' => now()->toDateString(),
            'year' => (int) date('Y'),
            'total_amount' => 5000.00,
            'funding_source' => '001',
            'status' => 'ACTIVA',
            'concept' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
