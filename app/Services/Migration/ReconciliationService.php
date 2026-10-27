<?php

namespace App\Services\Migration;

use App\Integrations\Ibmi\IbmiCertificationRepository;
use App\Integrations\Ibmi\IbmiExecutionRepository;
use App\Integrations\Ibmi\IbmiModificationRepository;
use App\Integrations\Ibmi\IbmiPoaRepository;
use App\Models\BudgetAccrual;
use App\Models\BudgetCeiling;
use App\Models\BudgetCertification;
use App\Models\PoaActivity;
use App\Models\PoaCertification;
use App\Models\PoaModificationRequest;

class ReconciliationService
{
    public function __construct(
        private readonly IbmiPoaRepository $poaRepo,
        private readonly IbmiCertificationRepository $certRepo,
        private readonly IbmiModificationRepository $modRepo,
        private readonly IbmiExecutionRepository $execRepo
    ) {}

    /**
     * Generar informe formal de reconciliación comparativo DB2 vs PostgreSQL.
     *
     * @return array{
     *     year: int,
     *     generated_at: string,
     *     integrity_hash: string,
     *     overall_status: string,
     *     tables: array<string, array{
     *         legacy_table: string,
     *         target_model: string,
     *         relationship_type: string,
     *         legacy_count: int,
     *         pg_count: int,
     *         count_diff: int,
     *         legacy_sum: float,
     *         pg_sum: float,
     *         sum_diff: float,
     *         status: string
     *     }>,
     *     relationship_classification: array<string, string>
     * }
     */
    public function generateReport(int $year): array
    {
        // 1. Extracción DTO desde repositorios IBM i
        $legacyActivities = $this->poaRepo->getPoaActivities($year);
        $legacyCeilings = $this->poaRepo->getBudgetCeilings($year);
        $legacyPoaCerts = $this->certRepo->getPoaCertifications($year);
        $legacyBudgetCerts = $this->certRepo->getBudgetCertifications($year);
        $legacyMods = $this->modRepo->getModifications($year);
        $legacyExec = $this->execRepo->getExecutionAccruals($year);

        // 2. Extracción desde PostgreSQL
        $pgActivities = PoaActivity::query()->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))->get();
        $pgCeilings = BudgetCeiling::query()->whereHas('planningPeriod', fn ($q) => $q->where('year', $year))->get();
        $pgPoaCerts = PoaCertification::query()->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))->get();
        $pgBudgetCerts = BudgetCertification::query()->where('year', $year)->get();
        $pgMods = PoaModificationRequest::query()->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))->get();
        $pgExec = BudgetAccrual::query()->where('year', $year)->where('status', 'ACTIVO')->get();

        $tables = [
            'PLAARC' => [
                'legacy_table' => 'PLAARC / PLAAR1 (POA/PAI)',
                'target_model' => 'poa_activities',
                'relationship_type' => 'B. Confirmada por Clave Lógica DDS (planum)',
                'legacy_count' => $legacyActivities->count(),
                'pg_count' => $pgActivities->count(),
                'count_diff' => $legacyActivities->count() - $pgActivities->count(),
                'legacy_sum' => (float) $legacyActivities->sum('annualAmount'),
                'pg_sum' => (float) $pgActivities->sum('annual_amount'),
                'sum_diff' => (float) $legacyActivities->sum('annualAmount') - (float) $pgActivities->sum('annual_amount'),
                'status' => abs((float) $legacyActivities->sum('annualAmount') - (float) $pgActivities->sum('annual_amount')) < 0.01 ? 'CONCILIADO' : 'DIFERENCIA_DETECTADA',
            ],
            'TECARC' => [
                'legacy_table' => 'TECARC / TECAR1 (Techos)',
                'target_model' => 'budget_ceilings',
                'relationship_type' => 'B. Confirmada por Clave Lógica DDS (tecsec)',
                'legacy_count' => $legacyCeilings->count(),
                'pg_count' => $pgCeilings->count(),
                'count_diff' => $legacyCeilings->count() - $pgCeilings->count(),
                'legacy_sum' => (float) $legacyCeilings->sum('totalAmount'),
                'pg_sum' => (float) $pgCeilings->sum('total_amount'),
                'sum_diff' => (float) $legacyCeilings->sum('totalAmount') - (float) $pgCeilings->sum('total_amount'),
                'status' => abs((float) $legacyCeilings->sum('totalAmount') - (float) $pgCeilings->sum('total_amount')) < 0.01 ? 'CONCILIADO' : 'DIFERENCIA_DETECTADA',
            ],
            'CERAR2' => [
                'legacy_table' => 'CERAR2 / LINARC (Certif. POA)',
                'target_model' => 'poa_certifications',
                'relationship_type' => 'C. Confirmada Funcionalmente por BPM (cernu2)',
                'legacy_count' => $legacyPoaCerts->count(),
                'pg_count' => $pgPoaCerts->count(),
                'count_diff' => $legacyPoaCerts->count() - $pgPoaCerts->count(),
                'legacy_sum' => (float) $legacyPoaCerts->sum('totalAmount'),
                'pg_sum' => (float) $pgPoaCerts->sum('total_amount'),
                'sum_diff' => (float) $legacyPoaCerts->sum('totalAmount') - (float) $pgPoaCerts->sum('total_amount'),
                'status' => abs((float) $legacyPoaCerts->sum('totalAmount') - (float) $pgPoaCerts->sum('total_amount')) < 0.01 ? 'CONCILIADO' : 'DIFERENCIA_DETECTADA',
            ],
            'CERA01' => [
                'legacy_table' => 'CERA01 / LINAR6 (Certif. Presup)',
                'target_model' => 'budget_certifications',
                'relationship_type' => 'B. Confirmada por Clave Lógica DDS (cernu2)',
                'legacy_count' => $legacyBudgetCerts->count(),
                'pg_count' => $pgBudgetCerts->count(),
                'count_diff' => $legacyBudgetCerts->count() - $pgBudgetCerts->count(),
                'legacy_sum' => (float) $legacyBudgetCerts->sum('totalAmount'),
                'pg_sum' => (float) $pgBudgetCerts->sum('total_amount'),
                'sum_diff' => (float) $legacyBudgetCerts->sum('totalAmount') - (float) $pgBudgetCerts->sum('total_amount'),
                'status' => abs((float) $legacyBudgetCerts->sum('totalAmount') - (float) $pgBudgetCerts->sum('total_amount')) < 0.01 ? 'CONCILIADO' : 'DIFERENCIA_DETECTADA',
            ],
            'SOLAR1' => [
                'legacy_table' => 'SOLAR1 / LINAR2 (Modificaciones)',
                'target_model' => 'poa_modification_requests',
                'relationship_type' => 'C. Confirmada Funcionalmente por BPM (solnu3)',
                'legacy_count' => $legacyMods->count(),
                'pg_count' => $pgMods->count(),
                'count_diff' => $legacyMods->count() - $pgMods->count(),
                'legacy_sum' => 0.0,
                'pg_sum' => 0.0,
                'sum_diff' => 0.0,
                'status' => $legacyMods->count() === $pgMods->count() ? 'CONCILIADO' : 'DIFERENCIA_DETECTADA',
            ],
            'LINAR1' => [
                'legacy_table' => 'LINAR1 (Devengados / CUR)',
                'target_model' => 'budget_accruals',
                'relationship_type' => 'C. Confirmada Funcionalmente por BPM (linco1)',
                'legacy_count' => $legacyExec->count(),
                'pg_count' => $pgExec->count(),
                'count_diff' => $legacyExec->count() - $pgExec->count(),
                'legacy_sum' => (float) $legacyExec->sum('accruedAmount'),
                'pg_sum' => (float) $pgExec->sum('accrued_amount'),
                'sum_diff' => (float) $legacyExec->sum('accruedAmount') - (float) $pgExec->sum('accrued_amount'),
                'status' => abs((float) $legacyExec->sum('accruedAmount') - (float) $pgExec->sum('accrued_amount')) < 0.01 ? 'CONCILIADO' : 'DIFERENCIA_DETECTADA',
            ],
        ];

        $hasMismatches = collect($tables)->contains(fn ($t) => $t['status'] === 'DIFERENCIA_DETECTADA' && ($t['legacy_count'] > 0 || $t['pg_count'] > 0));
        $overallStatus = $hasMismatches ? 'CON_DIFERENCIAS' : 'RECONCILIADO_OK';

        $reportJson = json_encode($tables);
        $integrityHash = hash('sha256', (string) $reportJson);

        return [
            'year' => $year,
            'generated_at' => now()->toIso8601String(),
            'integrity_hash' => $integrityHash,
            'overall_status' => $overallStatus,
            'tables' => $tables,
            'relationship_classification' => [
                'A' => 'CONFIRMADA FÍSICAMENTE (Claves declaradas)',
                'B' => 'CONFIRMADA POR CLAVE / ACCESO LÓGICO DDS',
                'C' => 'CONFIRMADA FUNCIONALMENTE POR BPM / PROGRAMAS',
                'D' => 'INFERIDA Y PENDIENTE DE VALIDACIÓN (No convertible a FK)',
            ],
        ];
    }
}
