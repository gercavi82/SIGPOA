<?php

namespace App\Actions\Execution;

use App\Contracts\EsigefGatewayInterface;
use App\Models\ImportBatch;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\UploadedFile;

class ImportExecutionCsvAction
{
    public function __construct(
        private readonly EsigefGatewayInterface $gateway,
        private readonly AuditService $auditService
    ) {}

    /**
     * @return array{batch: ImportBatch, rows_count: int, valid_count: int, invalid_count: int, unmatched_count: int}
     */
    public function execute(UploadedFile $file, int $year, ?int $month, User $user): array
    {
        $result = $this->gateway->importAccrualCsv($file, $year, $month, $user);

        $this->auditService->record(
            event: 'execution_batch.imported',
            auditable: $result['batch'],
            module: 'execution',
            newValues: [
                'batch_code' => $result['batch']->batch_code,
                'file_hash' => $result['batch']->file_hash,
                'total_rows' => $result['rows_count'],
                'valid_rows' => $result['valid_count'],
                'unmatched_rows' => $result['unmatched_count'],
                'invalid_rows' => $result['invalid_count'],
            ],
            reason: 'Importación de archivo CSV de ejecución eSIGEF'
        );

        return $result;
    }
}
