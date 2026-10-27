<?php

namespace Database\Factories;

use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportBatchFactory extends Factory
{
    protected $model = ImportBatch::class;

    public function definition(): array
    {
        return [
            'batch_code' => 'LOTE-'.fake()->unique()->numerify('##########'),
            'original_filename' => 'esigef_cur_export.csv',
            'file_hash' => fake()->unique()->sha256(),
            'year' => (int) date('Y'),
            'month' => 8,
            'status' => ImportBatchStatus::VALIDATED,
            'total_rows' => 1,
            'valid_rows' => 1,
            'invalid_rows' => 0,
            'unmatched_rows' => 0,
            'processed_rows' => 0,
            'imported_by' => User::factory(),
        ];
    }
}
