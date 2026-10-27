<?php

namespace App\Http\Controllers;

use App\Actions\Planning\AllocateActivityToPhaseAction;
use App\Http\Requests\StorePoaActivityPhaseAllocationRequest;
use App\Models\PoaActivity;
use DomainException;
use Illuminate\Http\RedirectResponse;

class PoaActivityPhaseAllocationController extends Controller
{
    public function store(StorePoaActivityPhaseAllocationRequest $request, PoaActivity $poaActivity, AllocateActivityToPhaseAction $allocate): RedirectResponse
    {
        try {
            $allocate->handle($poaActivity, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['allocation' => $exception->getMessage()]);
        }

        return back()->with('status', 'Actividad priorizada en la fase seleccionada.');
    }
}
