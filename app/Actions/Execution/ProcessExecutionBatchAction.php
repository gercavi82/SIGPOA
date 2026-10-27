<?php

namespace App\Actions\Execution;

use App\Enums\ExecutionClosingStatus;
use App\Enums\ImportBatchRowStatus;
use App\Enums\ImportBatchStatus;
use App\Models\BudgetAccrual;
use App\Models\ExecutionClosing;
use App\Models\ImportBatch;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessExecutionBatchAction
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * @return array{batch: ImportBatch, processed_count: int, total_accrued: float}
     */
    public function execute(ImportBatch $batch, User $user): array
    {
        if ($batch->status === ImportBatchStatus::PROCESSED) {
            throw new InvalidArgumentException("El lote {$batch->batch_code} ya se encuentra procesado.");
        }

        return DB::transaction(function () use ($batch, $user) {
            $candidateRows = $batch->rows()
                ->whereIn('status', [ImportBatchRowStatus::VALID, ImportBatchRowStatus::MATCHED])
                ->get();

            if ($candidateRows->isEmpty()) {
                throw new InvalidArgumentException('No existen filas válidas o conciliadas para procesar en este lote.');
            }

            $processedCount = 0;
            $totalAccrued = 0.0;

            foreach ($candidateRows as $row) {
                $accrualDate = $row->accrual_date ? Carbon::parse($row->accrual_date) : now();
                $year = $batch->year ?: (int) $accrualDate->year;
                $month = $batch->month ?: (int) $accrualDate->month;

                $isClosed = ExecutionClosing::query()
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('status', ExecutionClosingStatus::CLOSED)
                    ->exists();

                if ($isClosed) {
                    throw new InvalidArgumentException("El período {$year}-{$month} se encuentra CERRADO. No es posible procesar devengados para este mes.");
                }

                $accrual = BudgetAccrual::query()->updateOrCreate(
                    [
                        'year' => $year,
                        'cur_number' => $row->cur_number,
                        'cur_line' => $row->cur_line,
                        'poa_activity_id' => $row->poa_activity_id,
                    ],
                    [
                        'month' => $month,
                        'accrual_date' => $accrualDate->toDateString(),
                        'accrued_amount' => $row->amount,
                        'budget_certification_id' => $row->budget_certification_id,
                        'import_batch_id' => $batch->id,
                        'import_batch_row_id' => $row->id,
                        'status' => 'ACTIVO',
                        'created_by' => $user->id,
                    ]
                );

                $row->update([
                    'status' => ImportBatchRowStatus::PROCESSED,
                ]);

                $processedCount++;
                $totalAccrued += (float) $row->amount;
            }

            $remainingPending = $batch->rows()
                ->whereNotIn('status', [ImportBatchRowStatus::PROCESSED, ImportBatchRowStatus::IGNORED])
                ->count();

            $finalStatus = $remainingPending === 0 ? ImportBatchStatus::PROCESSED : ImportBatchStatus::PARTIAL;

            $batch->update([
                'status' => $finalStatus,
                'processed_rows' => $batch->rows()->where('status', ImportBatchRowStatus::PROCESSED)->count(),
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            $this->auditService->record(
                event: 'execution_batch.processed',
                auditable: $batch,
                module: 'execution',
                newValues: [
                    'batch_code' => $batch->batch_code,
                    'processed_count' => $processedCount,
                    'total_accrued' => $totalAccrued,
                    'final_status' => $finalStatus->value,
                ],
                reason: 'Procesamiento de lote de ejecución y registro de devengados'
            );

            return [
                'batch' => $batch->fresh(),
                'processed_count' => $processedCount,
                'total_accrued' => $totalAccrued,
            ];
        });
    }
}
