<?php

namespace App\Http\Controllers;

use App\Actions\Execution\CreateBudgetCertificationAction;
use App\Http\Requests\StoreBudgetCertificationRequest;
use App\Models\BudgetCertification;
use App\Models\PoaActivity;
use App\Models\PoaCertification;
use App\Models\PoaProcess;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class BudgetCertificationController extends Controller
{
    public function index(): View
    {
        $certifications = BudgetCertification::query()
            ->with(['lines.poaActivity', 'creator', 'poaCertification'])
            ->latest('id')
            ->paginate(15);

        return view('budget-certifications.index', [
            'certifications' => $certifications,
        ]);
    }

    public function create(): View
    {
        return view('budget-certifications.create', [
            'processes' => PoaProcess::query()->latest('id')->get(),
            'poaCertifications' => PoaCertification::query()->where('status', 'APROBADA')->latest('id')->get(),
            'activities' => PoaActivity::query()->where('status', 'APROBADA')->orderBy('number')->get(),
        ]);
    }

    public function store(StoreBudgetCertificationRequest $request, CreateBudgetCertificationAction $action): RedirectResponse
    {
        try {
            $certification = $action->execute($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['lines' => $e->getMessage()]);
        }

        return redirect()->route('budget-certifications.show', $certification)
            ->with('status', 'Certificación presupuestaria registrada exitosamente.');
    }

    public function show(BudgetCertification $budgetCertification): View
    {
        return view('budget-certifications.show', [
            'certification' => $budgetCertification->load(['lines.poaActivity', 'creator', 'poaCertification', 'accruals']),
        ]);
    }
}
