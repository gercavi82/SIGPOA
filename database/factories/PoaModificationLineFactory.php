<?php

namespace Database\Factories;

use App\Models\PoaActivity;
use App\Models\PoaModificationLine;
use App\Models\PoaModificationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoaModificationLine>
 */
class PoaModificationLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'poa_modification_request_id' => PoaModificationRequest::factory(),
            'source_activity_id' => PoaActivity::factory(),
            'destination_activity_id' => PoaActivity::factory(),
            'debit_amount' => 100.00,
            'credit_amount' => 100.00,
            'description' => fake()->sentence(),
        ];
    }
}
