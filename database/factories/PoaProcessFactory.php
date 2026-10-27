<?php

namespace Database\Factories;

use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use App\Models\PlanningPeriod;
use App\Models\PoaProcess;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoaProcess>
 */
class PoaProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planning_period_id' => PlanningPeriod::factory(),
            'phase' => PoaPhase::PoaNecesidades,
            'status' => ProcessStatus::Draft,
            'responsible_user_id' => User::factory(),
            'created_by' => User::factory(),
            'version' => 1,
        ];
    }
}
