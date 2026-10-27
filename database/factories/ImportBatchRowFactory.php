<?php

namespace Database\Factories;

use App\Enums\ImportBatchRowStatus;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\PoaActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportBatchRowFactory extends Factory
{
    protected $model = ImportBatchRow::class;

    public function definition(): array
    {
        return [
            'import_batch_id' => ImportBatch::factory(),
            'row_number' => 1,
            'cur_number' => (string) fake()->numberBetween(100000, 999999),
            'cur_line' => 1,
            'certification_number' => 'CP-'.fake()->numberBetween(100, 999),
            'activity_code' => 'ACT-001',
            'poa_activity_id' => PoaActivity::factory(),
            'accrual_date' => now()->toDateString(),
            'amount' => 1500.00,
            'status' => ImportBatchRowStatus::VALID,
        ];
    }
}
