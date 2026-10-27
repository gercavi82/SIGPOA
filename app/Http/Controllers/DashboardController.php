<?php

namespace App\Http\Controllers;

use App\Models\OrganizationalUnit;
use App\Models\PlanningPeriod;
use App\Repositories\AnalyticsRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsRepository $analyticsRepository
    ) {}

    /**
     * Handle the incoming dashboard request with institutional KPIs and execution charts.
     */
    public function __invoke(Request $request): View
    {
        $defaultYear = PlanningPeriod::query()->where('is_active', true)->value('year') ?? 2025;
        $year = $request->query('year') ? (int) $request->query('year') : $defaultYear;
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;

        $kpis = $this->analyticsRepository->getDashboardKpis($year, $unitId);
        $unitExecution = $this->analyticsRepository->getExecutionByUnit($year);
        $monthlyComparison = $this->analyticsRepository->getMonthlyExecutionComparison($year);
        $units = OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get();

        return view('dashboard', [
            'kpis' => $kpis,
            'unitExecution' => $unitExecution,
            'monthlyComparison' => $monthlyComparison,
            'units' => $units,
            'selectedYear' => $year,
            'selectedUnitId' => $unitId,
        ]);
    }
}
