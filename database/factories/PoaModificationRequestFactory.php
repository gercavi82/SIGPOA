<?php

namespace Database\Factories;

use App\Enums\ModificationStatus;
use App\Enums\ModificationType;
use App\Models\OrganizationalUnit;
use App\Models\PoaModificationRequest;
use App\Models\PoaProcess;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoaModificationRequest>
 */
class PoaModificationRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('MOD-###'),
            'type' => ModificationType::WithoutBudgetImpact,
            'status' => ModificationStatus::Draft,
            'poa_process_id' => PoaProcess::factory(),
            'organizational_unit_id' => OrganizationalUnit::factory(),
            'justification' => fake()->paragraph(),
            'requires_esigef' => false,
            'esigef_reform_number' => null,
            'reform_cur' => null,
            'requested_by' => User::factory(),
            'version' => 1,
        ];
    }
}
