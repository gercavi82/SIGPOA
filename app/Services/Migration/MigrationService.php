<?php

namespace App\Services\Migration;

use App\DTOs\Legacy\LegacyBudgetCeilingDto;
use App\DTOs\Legacy\LegacyBudgetCertificationDto;
use App\DTOs\Legacy\LegacyExecutionDto;
use App\DTOs\Legacy\LegacyModificationDto;
use App\DTOs\Legacy\LegacyPoaActivityDto;
use App\DTOs\Legacy\LegacyPoaCertificationDto;
use App\Integrations\Ibmi\IbmiCertificationRepository;
use App\Integrations\Ibmi\IbmiExecutionRepository;
use App\Integrations\Ibmi\IbmiModificationRepository;
use App\Integrations\Ibmi\IbmiPoaRepository;
use App\Models\BudgetAccrual;
use App\Models\BudgetCeiling;
use App\Models\BudgetCeilingUnit;
use App\Models\BudgetCertification;
use App\Models\BudgetCertificationLine;
use App\Models\OrganizationalUnit;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaActivityMonth;
use App\Models\PoaCertification;
use App\Models\PoaCertificationDetail;
use App\Models\PoaModificationLine;
use App\Models\PoaModificationRequest;
use App\Models\PoaProcess;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class MigrationService
{
    public function __construct(
        private readonly IbmiPoaRepository $poaRepo,
        private readonly IbmiCertificationRepository $certRepo,
        private readonly IbmiModificationRepository $modRepo,
        private readonly IbmiExecutionRepository $execRepo,
        private readonly ReconciliationService $reconciliationService,
        private readonly AuditService $auditService
    ) {}

    /**
     * Ejecuta el proceso de migración de datos legacy hacia PostgreSQL con inserción masiva por lotes (Bulk).
     *
     * @return array{
     *     year: int,
     *     dry_run: bool,
     *     migrated_activities: int,
     *     migrated_ceilings: int,
     *     migrated_certifications: int,
     *     migrated_budget_certifications: int,
     *     migrated_modifications: int,
     *     migrated_accruals: int,
     *     reconciliation_report: array<string, mixed>
     * }
     */
    public function migrateYear(int $year, User $user, bool $dryRun = false): array
    {
        // 1. Extracción de datos vía DTOs desde adaptadores IBM i
        $activities = $this->poaRepo->getPoaActivities($year);
        $ceilings = $this->poaRepo->getBudgetCeilings($year);
        $poaCerts = $this->certRepo->getPoaCertifications($year);
        $budgetCerts = $this->certRepo->getBudgetCertifications($year);
        $modifications = $this->modRepo->getModifications($year);
        $accruals = $this->execRepo->getExecutionAccruals($year);

        $counts = [
            'migrated_activities' => $activities->count(),
            'migrated_ceilings' => $ceilings->count(),
            'migrated_certifications' => $poaCerts->count(),
            'migrated_budget_certifications' => $budgetCerts->count(),
            'migrated_modifications' => $modifications->count(),
            'migrated_accruals' => $accruals->count(),
        ];

        if (! $dryRun) {
            DB::transaction(function () use ($year, $user, $counts, $activities, $ceilings, $poaCerts, $budgetCerts, $modifications, $accruals) {
                $now = now()->toDateTimeString();

                // Periodo de Planificación y Proceso POA
                $period = PlanningPeriod::query()->firstOrCreate(
                    ['year' => $year],
                    ['is_active' => true, 'starts_at' => "{$year}-01-01", 'ends_at' => "{$year}-12-31"]
                );

                $process = PoaProcess::query()->firstOrCreate(
                    ['planning_period_id' => $period->id, 'phase' => 'EJECUCION'],
                    ['status' => 'ABIERTO', 'opens_at' => "{$year}-01-01 00:00:00", 'responsible_user_id' => $user->id, 'created_by' => $user->id, 'version' => 1]
                );

                $unitsCache = OrganizationalUnit::query()->get()->keyBy('code');

                // 1. Migrar Unidades y Actividades POA
                $activityIds = [];
                $allActivityMonthsByKey = [];
                foreach ($activities as $dto) {
                    /** @var LegacyPoaActivityDto $dto */
                    $unit = $unitsCache->get($dto->unitCode);
                    if (! $unit) {
                        $unit = OrganizationalUnit::query()->create([
                            'code' => $dto->unitCode,
                            'name' => "Dirección {$dto->unitCode}",
                            'type' => 'DIRECCION',
                            'is_active' => true,
                        ]);
                        $unitsCache->put($dto->unitCode, $unit);
                    }

                    $activity = PoaActivity::query()->updateOrCreate(
                        [
                            'poa_process_id' => $process->id,
                            'number' => $dto->activityNumber,
                        ],
                        [
                            'organizational_unit_id' => $unit->id,
                            'description' => $dto->description ?: "Actividad {$dto->activityNumber}",
                            'objective' => $dto->objective ?: 'Objetivo institucional',
                            'priority' => $dto->priority,
                            'annual_target' => $dto->annualTarget,
                            'annual_amount' => $dto->annualAmount,
                            'status' => $dto->status,
                            'responsible_user_id' => $user->id,
                            'created_by' => $user->id,
                            'version' => 1,
                        ]
                    );

                    $activityIds[] = $activity->id;

                    foreach ($dto->months as $m) {
                        $mKey = $activity->id.'-'.$m['month'];
                        if (! isset($allActivityMonthsByKey[$mKey])) {
                            $allActivityMonthsByKey[$mKey] = [
                                'poa_activity_id' => $activity->id,
                                'month' => $m['month'],
                                'target' => $m['target'],
                                'amount' => $m['amount'],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        } else {
                            $allActivityMonthsByKey[$mKey]['amount'] += $m['amount'];
                        }
                    }
                }

                $allActivityMonths = array_values($allActivityMonthsByKey);

                // Limpiar e insertar mensualización masiva en bloques
                if (! empty($activityIds)) {
                    PoaActivityMonth::query()->whereIn('poa_activity_id', $activityIds)->delete();
                }
                if (! empty($allActivityMonths)) {
                    foreach (array_chunk($allActivityMonths, 1000) as $chunk) {
                        PoaActivityMonth::query()->insert($chunk);
                    }
                }

                // Cargar mapa de actividades en memoria
                $activitiesMap = PoaActivity::query()->where('poa_process_id', $process->id)->get()->keyBy('number');
                $defaultActivity = $activitiesMap->first();

                // 2. Migrar Techos
                foreach ($ceilings as $dto) {
                    /** @var LegacyBudgetCeilingDto $dto */
                    $ceiling = BudgetCeiling::query()->firstOrCreate(
                        [
                            'planning_period_id' => $period->id,
                            'source' => $dto->source,
                        ],
                        [
                            'current_expenditure' => $dto->totalAmount,
                            'investment_expenditure' => 0,
                            'total_amount' => $dto->totalAmount,
                            'status' => 'APROBADO',
                            'methodology' => 'Migrado desde DB2 (TECARC)',
                            'created_by' => $user->id,
                        ]
                    );

                    foreach ($dto->unitAllocations as $alloc) {
                        $unit = $unitsCache->get($alloc['unit_code']);
                        if ($unit) {
                            BudgetCeilingUnit::query()->updateOrCreate(
                                [
                                    'budget_ceiling_id' => $ceiling->id,
                                    'organizational_unit_id' => $unit->id,
                                ],
                                [
                                    'amount' => $alloc['amount'],
                                ]
                            );
                        }
                    }
                }

                // 3. Migrar Certificaciones POA
                $certIds = [];
                $allCertDetailsByKey = [];
                foreach ($poaCerts as $dto) {
                    /** @var LegacyPoaCertificationDto $dto */
                    $unit = $unitsCache->get($dto->unitCode) ?? $unitsCache->first();
                    $cert = PoaCertification::query()->updateOrCreate(
                        ['number' => $dto->certificationNumber],
                        [
                            'poa_process_id' => $process->id,
                            'organizational_unit_id' => $unit->id,
                            'subject' => $dto->subject,
                            'total_amount' => $dto->totalAmount,
                            'status' => $dto->status,
                            'requested_by' => $user->id,
                            'version' => 1,
                        ]
                    );

                    $certIds[] = $cert->id;

                    foreach ($dto->lines as $line) {
                        $act = $activitiesMap->get($line['activity_number']) ?? $defaultActivity;
                        if ($act) {
                            $cKey = $cert->id.'-'.$act->id;
                            if (! isset($allCertDetailsByKey[$cKey])) {
                                $allCertDetailsByKey[$cKey] = [
                                    'poa_certification_id' => $cert->id,
                                    'poa_activity_id' => $act->id,
                                    'amount' => $line['amount'],
                                    'description' => $line['description'],
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            } else {
                                $allCertDetailsByKey[$cKey]['amount'] += $line['amount'];
                            }
                        }
                    }
                }

                $allCertDetails = array_values($allCertDetailsByKey);

                if (! empty($certIds)) {
                    PoaCertificationDetail::query()->whereIn('poa_certification_id', $certIds)->delete();
                }
                if (! empty($allCertDetails)) {
                    foreach (array_chunk($allCertDetails, 1000) as $chunk) {
                        PoaCertificationDetail::query()->insert($chunk);
                    }
                }

                // 4. Migrar Certificaciones Presupuestarias
                $bCertIds = [];
                $allBudgetLinesByKey = [];
                foreach ($budgetCerts as $dto) {
                    /** @var LegacyBudgetCertificationDto $dto */
                    $bCert = BudgetCertification::query()->updateOrCreate(
                        [
                            'year' => $year,
                            'certification_number' => $dto->certificationNumber,
                        ],
                        [
                            'poa_process_id' => $process->id,
                            'certification_date' => $dto->certificationDate,
                            'total_amount' => $dto->totalAmount,
                            'concept' => $dto->concept,
                            'status' => $dto->status,
                            'created_by' => $user->id,
                        ]
                    );

                    $bCertIds[] = $bCert->id;

                    foreach ($dto->lines as $idx => $line) {
                        $lineNum = $line['line_number'] ?? ($idx + 1);
                        $bKey = $bCert->id.'-'.$lineNum;
                        if (! isset($allBudgetLinesByKey[$bKey])) {
                            $allBudgetLinesByKey[$bKey] = [
                                'budget_certification_id' => $bCert->id,
                                'line_number' => $lineNum,
                                'amount' => $line['amount'],
                                'concept' => $line['concept'],
                                'status' => 'ACTIVA',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        } else {
                            $allBudgetLinesByKey[$bKey]['amount'] += $line['amount'];
                        }
                    }
                }

                $allBudgetLines = array_values($allBudgetLinesByKey);

                if (! empty($bCertIds)) {
                    BudgetCertificationLine::query()->whereIn('budget_certification_id', $bCertIds)->delete();
                }
                if (! empty($allBudgetLines)) {
                    foreach (array_chunk($allBudgetLines, 1000) as $chunk) {
                        BudgetCertificationLine::query()->insert($chunk);
                    }
                }

                $budgetCertsMap = BudgetCertification::query()->where('year', $year)->get()->keyBy('certification_number');

                // 5. Migrar Modificaciones
                $modIds = [];
                $allModLines = [];
                foreach ($modifications as $dto) {
                    /** @var LegacyModificationDto $dto */
                    $unit = $unitsCache->get($dto->unitCode) ?? $unitsCache->first();
                    $mod = PoaModificationRequest::query()->updateOrCreate(
                        ['number' => $dto->requestNumber],
                        [
                            'poa_process_id' => $process->id,
                            'organizational_unit_id' => $unit->id,
                            'type' => $dto->type,
                            'status' => $dto->status,
                            'justification' => 'Migrado desde DB2 (SOLAR1)',
                            'esigef_reform_number' => $dto->esigefReformNumber,
                            'reform_cur' => $dto->reformCur,
                            'requested_by' => $user->id,
                            'version' => 1,
                        ]
                    );

                    $modIds[] = $mod->id;

                    foreach ($dto->lines as $line) {
                        $src = $line['source_activity'] ? $activitiesMap->get($line['source_activity']) : null;
                        $dst = $line['destination_activity'] ? $activitiesMap->get($line['destination_activity']) : null;

                        $allModLines[] = [
                            'poa_modification_request_id' => $mod->id,
                            'source_poa_activity_id' => $src?->id,
                            'destination_poa_activity_id' => $dst?->id,
                            'debit_amount' => $line['debit_amount'],
                            'credit_amount' => $line['credit_amount'],
                            'description' => $line['description'] ?: 'Línea migrada',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (! empty($modIds)) {
                    PoaModificationLine::query()->whereIn('poa_modification_request_id', $modIds)->delete();
                }
                if (! empty($allModLines)) {
                    foreach (array_chunk($allModLines, 1000) as $chunk) {
                        PoaModificationLine::query()->insert($chunk);
                    }
                }

                // 6. Migrar Devengados (LINAR1) en bloques limpios y consolidados
                $allAccrualsByKey = [];
                foreach ($accruals as $dto) {
                    /** @var LegacyExecutionDto $dto */
                    $act = $activitiesMap->get($dto->activityNumber) ?? $defaultActivity;
                    $bCert = $dto->certificationNumber ? $budgetCertsMap->get($dto->certificationNumber) : null;

                    if ($act) {
                        $key = $dto->year.'-'.$dto->curNumber.'-'.$dto->curLine.'-'.$act->id;
                        if (! isset($allAccrualsByKey[$key])) {
                            $allAccrualsByKey[$key] = [
                                'year' => $dto->year,
                                'cur_number' => $dto->curNumber,
                                'cur_line' => $dto->curLine,
                                'poa_activity_id' => $act->id,
                                'month' => $dto->month,
                                'accrual_date' => $dto->accrualDate,
                                'accrued_amount' => $dto->accruedAmount,
                                'budget_certification_id' => $bCert?->id,
                                'status' => $dto->status,
                                'created_by' => $user->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        } else {
                            $allAccrualsByKey[$key]['accrued_amount'] += $dto->accruedAmount;
                        }
                    }
                }

                $allAccruals = array_values($allAccrualsByKey);
                BudgetAccrual::query()->where('year', $year)->delete();
                if (! empty($allAccruals)) {
                    foreach (array_chunk($allAccruals, 1000) as $chunk) {
                        BudgetAccrual::query()->insert($chunk);
                    }
                }

                $this->auditService->record(
                    event: 'migration.executed',
                    module: 'migration',
                    newValues: array_merge(['year' => $year], $counts),
                    reason: 'Ejecución formal de migración DB2 -> PostgreSQL'
                );
            });
        }

        $reconciliationReport = $this->reconciliationService->generateReport($year);

        return array_merge([
            'year' => $year,
            'dry_run' => $dryRun,
            'reconciliation_report' => $reconciliationReport,
        ], $counts);
    }
}
