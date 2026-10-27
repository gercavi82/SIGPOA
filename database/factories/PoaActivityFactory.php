<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Models\OrganizationalUnit;
use App\Models\PoaActivity;
use App\Models\PoaProcess;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoaActivity>
 */
class PoaActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'poa_process_id' => PoaProcess::factory(), 'organizational_unit_id' => OrganizationalUnit::factory(),
            'number' => fake()->unique()->numerify('ACT-###'), 'description' => fake()->sentence(), 'objective' => fake()->sentence(),
            'status' => ActivityStatus::Draft, 'annual_target' => 1, 'annual_amount' => 100,
            'responsible_user_id' => User::factory(), 'created_by' => User::factory(), 'version' => 1,
        ];
    }
}
