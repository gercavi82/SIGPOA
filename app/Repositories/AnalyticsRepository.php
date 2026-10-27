<?php

namespace App\Repositories;

use App\Models\BudgetAccrual;
use App\Models\OrganizationalUnit;
use App\Models\PoaActivity;
use App\Models\PoaActivityMonth;
use App\Models\PoaCertification;
use App\Models\PoaModificationRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository
{
    /**
     * Get high level dashboard KPIs for a given year and optional organizational unit.
     *
     * @return array{
     *     year: int,
     *     total_poa: float,
     *     total_planned: float,
     *     total_accrued: float,
     *     balance: float,
     *     execution_rate: float,
     *     activities_count: int,
     *     pending_certifications_count: int,
     *     pending_modifications_count: int,
     *     observed_requests_count: int
     * }
     */
    public function getDashboardKpis(?int $year = null, ?int $unitId = null): array
    {
        $year = $year ?: (int) date('Y');

        $activitiesQuery = PoaActivity::query()
            ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year));

        if ($unitId) {
            $activitiesQuery->where('organizational_unit_id', $unitId);
        }

        $activitiesCount = (int) (clone $activitiesQuery)->count();
        $totalPoa = (float) (clone $activitiesQuery)->sum('annual_amount');

        $activityIds = (clone $activitiesQuery)->pluck('id');

        $totalPlanned = (float) PoaActivityMonth::query()
            ->whereIn('poa_activity_id', $activityIds)
            ->sum('amount');

        $totalAccrued = (float) BudgetAccrual::query()
            ->where('year', $year)
            ->where('status', 'ACTIVO')
            ->when($unitId, fn ($q) => $q->whereIn('poa_activity_id', $activityIds))
            ->sum('accrued_amount');

        $balance = max(0, $totalPoa - $totalAccrued);
        $executionRate = $totalPoa > 0 ? round(($totalAccrued / $totalPoa) * 100, 2) : 0.0;

        $pendingCertifications = PoaCertification::query()
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->whereNotIn('status', ['APROBADA', 'NEGADA', 'FINALIZADA'])
            ->count();

        $pendingModifications = PoaModificationRequest::query()
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->whereNotIn('status', ['APROBADA', 'NEGADA'])
            ->count();

        $observedCertifications = PoaCertification::query()
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->whereIn('status', ['DEVUELTA_AREA', 'DEVUELTA_DPGE'])
            ->count();

        $observedModifications = PoaModificationRequest::query()
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->where('status', 'DEVUELTA')
            ->count();

        return [
            'year' => $year,
            'total_poa' => $totalPoa,
            'total_planned' => $totalPlanned,
            'total_accrued' => $totalAccrued,
            'balance' => $balance,
            'execution_rate' => $executionRate,
            'activities_count' => $activitiesCount,
            'pending_certifications_count' => $pendingCertifications,
            'pending_modifications_count' => $pendingModifications,
            'observed_requests_count' => $observedCertifications + $observedModifications,
        ];
    }

    /**
     * Get aggregated execution metrics grouped by Organizational Unit (Dirección).
     *
     * @return Collection<int, array{
     *     unit_id: int,
     *     unit_code: string,
     *     unit_name: string,
     *     activities_count: int,
     *     total_poa: float,
     *     total_accrued: float,
     *     balance: float,
     *     execution_rate: float
     * }>
     */
    public function getExecutionByUnit(?int $year = null): Collection
    {
        $year = $year ?: (int) date('Y');

        $units = OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get();

        return $units->map(function (OrganizationalUnit $unit) use ($year) {
            $activities = PoaActivity::query()
                ->where('organizational_unit_id', $unit->id)
                ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))
                ->get();

            $activitiesCount = $activities->count();
            $totalPoa = (float) $activities->sum('annual_amount');

            $activityIds = $activities->pluck('id');
            $totalAccrued = (float) BudgetAccrual::query()
                ->where('year', $year)
                ->where('status', 'ACTIVO')
                ->whereIn('poa_activity_id', $activityIds)
                ->sum('accrued_amount');

            $balance = max(0, $totalPoa - $totalAccrued);
            $executionRate = $totalPoa > 0 ? round(($totalAccrued / $totalPoa) * 100, 2) : 0.0;

            return [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'unit_name' => $unit->name,
                'activities_count' => $activitiesCount,
                'total_poa' => $totalPoa,
                'total_accrued' => $totalAccrued,
                'balance' => $balance,
                'execution_rate' => $executionRate,
            ];
        });
    }

    /**
     * Get monthly execution comparison (Planificado vs Devengado).
     *
     * @return array<int, array{
     *     month: int,
     *     month_name: string,
     *     planned: float,
     *     accrued: float,
     *     rate: float
     * }>
     */
    public function getMonthlyExecutionComparison(?int $year = null): array
    {
        $year = $year ?: (int) date('Y');
        $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $plannedByMonth = PoaActivityMonth::query()
            ->whereHas('activity.process.planningPeriod', fn ($q) => $q->where('year', $year))
            ->select('month', DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $accruedByMonth = BudgetAccrual::query()
            ->where('year', $year)
            ->where('status', 'ACTIVO')
            ->select('month', DB::raw('SUM(accrued_amount) as total'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $planned = (float) ($plannedByMonth[$m] ?? 0.0);
            $accrued = (float) ($accruedByMonth[$m] ?? 0.0);
            $rate = $planned > 0 ? round(($accrued / $planned) * 100, 2) : 0.0;

            $result[] = [
                'month' => $m,
                'month_name' => $monthNames[$m - 1],
                'planned' => $planned,
                'accrued' => $accrued,
                'rate' => $rate,
            ];
        }

        return $result;
    }

    /**
     * Return dataset projection compatible with V_PRO_SIGPOA_EJE for Superset BI.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAnalyticalDataSet(?int $year = null): array
    {
        $year = $year ?: (int) date('Y');

        $activities = PoaActivity::query()
            ->with(['organizationalUnit', 'process.planningPeriod'])
            ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))
            ->get();

        $accrualsByActivity = BudgetAccrual::query()
            ->where('year', $year)
            ->where('status', 'ACTIVO')
            ->select('poa_activity_id', DB::raw('SUM(accrued_amount) as total'))
            ->groupBy('poa_activity_id')
            ->pluck('total', 'poa_activity_id')
            ->toArray();

        return $activities->map(function (PoaActivity $act) use ($accrualsByActivity, $year) {
            $accrued = (float) ($accrualsByActivity[$act->id] ?? 0.0);
            $planned = (float) $act->annual_amount;
            $balance = max(0, $planned - $accrued);
            $pct = $planned > 0 ? round(($accrued / $planned) * 100, 2) : 0.0;

            return [
                'ejercicio' => $year,
                'unidad_codigo' => $act->organizationalUnit?->code ?? 'N/A',
                'unidad_nombre' => $act->organizationalUnit?->name ?? 'N/A',
                'actividad_numero' => $act->number,
                'actividad_descripcion' => $act->description,
                'monto_planificado' => $planned,
                'monto_devengado' => $accrued,
                'saldo_disponible' => $balance,
                'porcentaje_ejecucion' => $pct,
                'estado' => $act->status->value,
            ];
        })->toArray();
    }
}
