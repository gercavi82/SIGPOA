<?php

namespace App\Actions\Execution;

use App\Enums\ImportBatchRowStatus;
use App\Enums\ImportBatchStatus;
use App\Models\BudgetCertification;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\PoaActivity;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReconcileBatchRowAction
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * @param  array{
     *     poa_activity_id?: ?int,
     *     budget_certification_id?: ?int
     * }  $data
     */
    public function execute(ImportBatchRow $row, array $data, User $user): ImportBatchRow
    {
        if ($row->status === ImportBatchRowStatus::PROCESSED) {
            throw new InvalidArgumentException('No se puede conciliar una fila que ya fue procesada.');
        }

        $activityId = $data['poa_activity_id'] ?? $row->poa_activity_id;
        $certificationId = $data['budget_certification_id'] ?? $row->budget_certification_id;

        if (! $activityId) {
            throw new InvalidArgumentException('Debe seleccionar una actividad POA válida para conciliar la fila.');
        }

        $activity = PoaActivity::query()->findOrFail($activityId);
        $certification = $certificationId ? BudgetCertification::query()->find($certificationId) : null;

        return DB::transaction(function () use ($row, $activity, $certification) {
            $oldValues = $row->toArray();

            $row->update([
                'poa_activity_id' => $activity->id,
                'budget_certification_id' => $certification?->id,
                'status' => ImportBatchRowStatus::MATCHED,
                'error_message' => null,
            ]);

            $this->recalculateBatch($row->batch);

            $this->auditService->record(
                event: 'execution_batch_row.reconciled',
                auditable: $row,
                module: 'execution',
                oldValues: $oldValues,
                newValues: $row->toArray(),
                reason: 'Conciliación manual de fila de ejecución eSIGEF'
            );

            return $row->fresh(['poaActivity', 'budgetCertification']);
        });
    }

    private function recalculateBatch(ImportBatch $batch): void
    {
        $rows = $batch->rows()->get();
        $totalRows = $rows->count();
        $validRows = $rows->whereIn('status', [ImportBatchRowStatus::VALID, ImportBatchRowStatus::MATCHED])->count();
        $invalidRows = $rows->where('status', ImportBatchRowStatus::INVALID)->count();
        $unmatchedRows = $rows->where('status', ImportBatchRowStatus::UNMATCHED)->count();
        $processedRows = $rows->where('status', ImportBatchRowStatus::PROCESSED)->count();

        $status = $batch->status;
        if ($batch->status !== ImportBatchStatus::PROCESSED) {
            if ($invalidRows === 0 && $unmatchedRows === 0) {
                $status = ImportBatchStatus::VALIDATED;
            } elseif ($validRows > 0) {
                $status = ImportBatchStatus::PARTIAL;
            } else {
                $status = ImportBatchStatus::FAILED;
            }
        }

        $batch->update([
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'unmatched_rows' => $unmatchedRows,
            'processed_rows' => $processedRows,
            'status' => $status,
        ]);
    }
}
