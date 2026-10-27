<?php

namespace App\Actions\Execution;

use App\Models\BudgetCertification;
use App\Models\BudgetCertificationLine;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateBudgetCertificationAction
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * @param  array{
     *     poa_process_id?: ?int,
     *     poa_certification_id?: ?int,
     *     certification_number: string,
     *     certification_date: string,
     *     year: int,
     *     total_amount: float,
     *     funding_source?: ?string,
     *     concept?: ?string,
     *     lines: array<int, array{
     *         line_number?: int,
     *         concept?: ?string,
     *         poa_activity_id?: ?int,
     *         funding_source?: ?string,
     *         amount: float
     *     }>
     * }  $data
     */
    public function execute(array $data, User $user): BudgetCertification
    {
        $lines = $data['lines'] ?? [];
        if (empty($lines)) {
            throw new InvalidArgumentException('La certificación presupuestaria debe contener al menos una línea.');
        }

        $sumLines = array_sum(array_column($lines, 'amount'));
        if (abs($sumLines - (float) $data['total_amount']) > 0.01) {
            throw new InvalidArgumentException("La suma de las líneas ({$sumLines}) no coincide con el monto total ({$data['total_amount']}).");
        }

        return DB::transaction(function () use ($data, $lines, $user) {
            $certification = BudgetCertification::query()->create([
                'poa_process_id' => $data['poa_process_id'] ?? null,
                'poa_certification_id' => $data['poa_certification_id'] ?? null,
                'certification_number' => $data['certification_number'],
                'certification_date' => $data['certification_date'],
                'year' => $data['year'],
                'total_amount' => $data['total_amount'],
                'funding_source' => $data['funding_source'] ?? null,
                'concept' => $data['concept'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($lines as $index => $line) {
                BudgetCertificationLine::query()->create([
                    'budget_certification_id' => $certification->id,
                    'line_number' => $line['line_number'] ?? ($index + 1),
                    'concept' => $line['concept'] ?? null,
                    'poa_activity_id' => $line['poa_activity_id'] ?? null,
                    'funding_source' => $line['funding_source'] ?? ($data['funding_source'] ?? null),
                    'amount' => $line['amount'],
                ]);
            }

            $this->auditService->record(
                event: 'budget_certification.created',
                auditable: $certification,
                module: 'budget_certifications',
                newValues: $certification->toArray(),
                reason: 'Registro de certificación presupuestaria'
            );

            return $certification->load('lines.poaActivity');
        });
    }
}
