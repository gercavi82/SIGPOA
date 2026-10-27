<?php

namespace Database\Factories;

use App\Models\BudgetCertification;
use App\Models\BudgetCertificationLine;
use App\Models\PoaActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetCertificationLineFactory extends Factory
{
    protected $model = BudgetCertificationLine::class;

    public function definition(): array
    {
        return [
            'budget_certification_id' => BudgetCertification::factory(),
            'line_number' => 1,
            'concept' => fake()->sentence(),
            'poa_activity_id' => PoaActivity::factory(),
            'funding_source' => '001',
            'amount' => 5000.00,
            'status' => 'ACTIVA',
        ];
    }
}
