<?php

namespace Database\Factories;

use App\Enums\ExecutionClosingStatus;
use App\Models\ExecutionClosing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExecutionClosingFactory extends Factory
{
    protected $model = ExecutionClosing::class;

    public function definition(): array
    {
        return [
            'year' => (int) date('Y'),
            'month' => 8,
            'status' => ExecutionClosingStatus::CLOSED,
            'total_planned' => 50000.00,
            'total_accrued' => 45000.00,
            'notes' => 'Cierre mensual de prueba',
            'closed_by' => User::factory(),
            'closed_at' => now(),
        ];
    }
}
