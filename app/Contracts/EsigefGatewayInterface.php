<?php

namespace App\Contracts;

use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface EsigefGatewayInterface
{
    /**
     * Parse and stage an eSIGEF CSV file into an ImportBatch with rows.
     *
     * @return array{batch: ImportBatch, rows_count: int, valid_count: int, invalid_count: int, unmatched_count: int}
     */
    public function importAccrualCsv(UploadedFile $file, int $year, ?int $month, User $user): array;

    /**
     * Validate CSV row payload structure.
     *
     * @param  array<string, mixed>  $row
     * @return array{is_valid: bool, error: ?string, data: array<string, mixed>}
     */
    public function validateRow(array $row): array;
}
