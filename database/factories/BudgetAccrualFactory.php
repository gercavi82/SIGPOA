<?php

namespace Database\Factories;

use App\Models\BudgetAccrual;
use App\Models\PoaActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetAccrualFactory extends Factory
{
    protected $model = BudgetAccrual::class;

    public function definition(): array
    {
        return [
            'cur_number' => (string) fake()->unique()->numberBetween(100000, 999999),
            'cur_line' => 1,
            'year' => (int) date('Y'),
            'month' => 8,
            'accrual_date' => now()->toDateString(),
            'accrued_amount' => 1500.00,
            'poa_activity_id' => PoaActivity::factory(),
            'status' => 'ACTIVO',
            'created_by' => User::factory(),
        ];
    }
}
