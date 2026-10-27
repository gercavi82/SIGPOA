<?php

namespace App\Actions\Execution;

use App\Enums\ExecutionClosingStatus;
use App\Models\BudgetAccrual;
use App\Models\ExecutionClosing;
use App\Models\PoaActivityMonth;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CloseExecutionMonthAction
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * @param  array{
     *     year: int,
     *     month: int,
     *     notes?: ?string
     * }  $data
     */
    public function execute(array $data, User $user): ExecutionClosing
    {
        $year = (int) $data['year'];
        $month = (int) $data['month'];

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('El mes debe estar comprendido entre 1 y 12.');
        }

        $existing = ExecutionClosing::query()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existing && $existing->status === ExecutionClosingStatus::CLOSED) {
            throw new InvalidArgumentException("El período {$year}-{$month} ya se encuentra cerrado.");
        }

        return DB::transaction(function () use ($year, $month, $data, $user, $existing) {
            $totalPlanned = (float) PoaActivityMonth::query()
                ->where('month', $month)
                ->whereHas('activity.process.planningPeriod', fn ($q) => $q->where('year', $year))
                ->sum('amount');

            $totalAccrued = (float) BudgetAccrual::query()
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'ACTIVO')
                ->sum('accrued_amount');

            $closing = $existing ?? new ExecutionClosing;
            $closing->fill([
                'year' => $year,
                'month' => $month,
                'status' => ExecutionClosingStatus::CLOSED,
                'total_planned' => $totalPlanned,
                'total_accrued' => $totalAccrued,
                'notes' => $data['notes'] ?? null,
                'closed_by' => $user->id,
                'closed_at' => now(),
            ]);
            $closing->save();

            $this->auditService->record(
                event: 'execution.month_closed',
                auditable: $closing,
                module: 'execution',
                newValues: [
                    'year' => $year,
                    'month' => $month,
                    'total_planned' => $totalPlanned,
                    'total_accrued' => $totalAccrued,
                ],
                reason: 'Cierre formal mensual de ejecución presupuestaria'
            );

            return $closing;
        });
    }
}
