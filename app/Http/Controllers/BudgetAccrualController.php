<?php

namespace App\Http\Controllers;

use App\Models\BudgetAccrual;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetAccrualController extends Controller
{
    public function index(Request $request): View
    {
        $defaultYear = PlanningPeriod::query()->where('is_active', true)->value('year') ?? 2025;
        $year = $request->query('year', $defaultYear);
        $month = $request->query('month');
        $cur = $request->query('cur');
        $activityId = $request->query('activity_id');

        $query = BudgetAccrual::query()
            ->with(['activity', 'budgetCertification', 'batch', 'creator'])
            ->where('year', $year);

        if ($month) {
            $query->where('month', (int) $month);
        }

        if ($cur) {
            $query->where('cur_number', 'like', "%{$cur}%");
        }

        if ($activityId) {
            $query->where('poa_activity_id', (int) $activityId);
        }

        $accruals = $query->latest('accrual_date')->latest('id')->paginate(20)->withQueryString();
        $totalAccrued = (float) (clone $query)->sum('accrued_amount');

        $activities = PoaActivity::query()->orderBy('number')->get();

        return view('budget-accruals.index', [
            'accruals' => $accruals,
            'totalAccrued' => $totalAccrued,
            'activities' => $activities,
            'selectedYear' => (int) $year,
            'selectedMonth' => $month ? (int) $month : null,
            'selectedCur' => $cur,
            'selectedActivityId' => $activityId ? (int) $activityId : null,
        ]);
    }
}
