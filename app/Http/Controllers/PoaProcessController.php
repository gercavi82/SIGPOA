<?php

namespace App\Http\Controllers;

use App\Actions\Planning\CreatePoaProcessAction;
use App\Actions\Planning\TransitionPoaProcessAction;
use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use App\Http\Requests\StorePoaProcessRequest;
use App\Http\Requests\TransitionPoaProcessRequest;
use App\Models\PlanningPeriod;
use App\Models\PoaProcess;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PoaProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $processes = PoaProcess::query()->with(['planningPeriod', 'responsible'])->latest()->paginate(15);

        return view('poa-processes.index', compact('processes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('poa-processes.create', ['periods' => PlanningPeriod::query()->latest('year')->get(), 'users' => User::query()->where('is_active', true)->orderBy('name')->get(), 'phases' => PoaPhase::cases()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePoaProcessRequest $request, CreatePoaProcessAction $create): RedirectResponse
    {
        $create->handle($request->validated(), $request->user());

        return redirect()->route('poa-processes.index')->with('status', 'Proceso creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PoaProcess $poaProcess): View
    {
        return view('poa-processes.show', ['process' => $poaProcess->load(['planningPeriod', 'responsible']), 'statuses' => $poaProcess->status->allowedTransitions()]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function transition(TransitionPoaProcessRequest $request, PoaProcess $poaProcess, TransitionPoaProcessAction $transition): RedirectResponse
    {
        try {
            $transition->handle($poaProcess, ProcessStatus::from($request->validated('status')), $request->user(), $request->validated('comment'), $request->integer('version'));
        } catch (DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Estado actualizado correctamente.');
    }
}
