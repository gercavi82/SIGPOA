<?php

namespace App\Http\Controllers;

use App\Actions\BudgetCeilings\SaveBudgetCeilingAction;
use App\Actions\BudgetCeilings\TransitionBudgetCeilingAction;
use App\Enums\BudgetCeilingStatus;
use App\Http\Requests\StoreBudgetCeilingRequest;
use App\Http\Requests\TransitionBudgetCeilingRequest;
use App\Models\BudgetCeiling;
use App\Models\OrganizationalUnit;
use App\Models\PlanningPeriod;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BudgetCeilingController extends Controller
{
    public function index(): View
    {
        return view('budget-ceilings.index', ['ceilings' => BudgetCeiling::query()->with('planningPeriod')->withSum('units', 'amount')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('budget-ceilings.create', ['periods' => PlanningPeriod::query()->latest('year')->get(), 'units' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(StoreBudgetCeilingRequest $request, SaveBudgetCeilingAction $save): RedirectResponse
    {
        try {
            $ceiling = $save->handle($request->validated(), $request->user());
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['ceiling' => $exception->getMessage()]);
        }

        return redirect()->route('budget-ceilings.show', $ceiling)->with('status', 'Techo registrado.');
    }

    public function show(BudgetCeiling $budgetCeiling): View
    {
        return view('budget-ceilings.show', ['ceiling' => $budgetCeiling->load(['planningPeriod', 'units.organizationalUnit']), 'statuses' => $budgetCeiling->status->allowedTransitions()]);
    }

    public function transition(TransitionBudgetCeilingRequest $request, BudgetCeiling $budgetCeiling, TransitionBudgetCeilingAction $transition): RedirectResponse
    {
        try {
            $transition->handle($budgetCeiling, BudgetCeilingStatus::from($request->validated('status')), $request->user(), $request->validated('comment'), $request->integer('version'));
        } catch (DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Estado actualizado.');
    }
}
