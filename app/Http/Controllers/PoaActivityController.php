<?php

namespace App\Http\Controllers;

use App\Actions\Planning\SavePoaActivityAction;
use App\Actions\Planning\TransitionPoaActivityAction;
use App\Enums\ActivityStatus;
use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use App\Http\Requests\StorePoaActivityRequest;
use App\Http\Requests\TransitionPoaActivityRequest;
use App\Models\OrganizationalUnit;
use App\Models\PoaActivity;
use App\Models\PoaProcess;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PoaActivityController extends Controller
{
    public function index(): View
    {
        return view('poa-activities.index', ['activities' => PoaActivity::query()->with(['process.planningPeriod', 'organizationalUnit', 'responsible'])->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('poa-activities.create', ['processes' => PoaProcess::query()->where('phase', PoaPhase::PoaNecesidades)->where('status', ProcessStatus::Open)->with('planningPeriod')->get(), 'units' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get(), 'users' => User::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(StorePoaActivityRequest $request, SavePoaActivityAction $save): RedirectResponse
    {
        try {
            $activity = $save->handle($request->validated(), $request->user());
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['activity' => $exception->getMessage()]);
        }

        return redirect()->route('poa-activities.show', $activity)->with('status', 'Actividad guardada.');
    }

    public function show(PoaActivity $poaActivity): View
    {
        return view('poa-activities.show', ['activity' => $poaActivity->load(['process.planningPeriod', 'organizationalUnit', 'responsible', 'months']), 'statuses' => $poaActivity->status->allowedTransitions(), 'targetProcesses' => PoaProcess::query()->whereIn('phase', [PoaPhase::Proforma, PoaPhase::PoaInicial])->where('status', ProcessStatus::Open)->with('planningPeriod')->get()]);
    }

    public function edit(PoaActivity $poaActivity): View
    {
        abort_unless(in_array($poaActivity->status, [ActivityStatus::Draft, ActivityStatus::ReturnedArea, ActivityStatus::ReturnedDpge], true), 403);

        return view('poa-activities.edit', ['activity' => $poaActivity->load('months'), 'units' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get(), 'users' => User::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function update(StorePoaActivityRequest $request, PoaActivity $poaActivity, SavePoaActivityAction $save): RedirectResponse
    {
        try {
            $save->handle($request->validated(), $request->user(), $poaActivity);
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['activity' => $exception->getMessage()]);
        }

        return redirect()->route('poa-activities.show', $poaActivity)->with('status', 'Corrección guardada y versionada.');
    }

    public function transition(TransitionPoaActivityRequest $request, PoaActivity $poaActivity, TransitionPoaActivityAction $transition): RedirectResponse
    {
        try {
            $transition->handle($poaActivity, ActivityStatus::from($request->validated('status')), $request->user(), $request->validated('comment'), $request->integer('version'));
        } catch (DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Estado actualizado.');
    }
}
