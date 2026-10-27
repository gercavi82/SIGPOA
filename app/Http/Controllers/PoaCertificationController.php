<?php

namespace App\Http\Controllers;

use App\Actions\Certifications\CreatePoaCertificationAction;
use App\Actions\Certifications\TransitionPoaCertificationAction;
use App\Enums\CertificationStatus;
use App\Http\Requests\StorePoaCertificationRequest;
use App\Http\Requests\TransitionPoaCertificationRequest;
use App\Models\OrganizationalUnit;
use App\Models\PoaActivity;
use App\Models\PoaCertification;
use App\Models\PoaProcess;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PoaCertificationController extends Controller
{
    public function index(): View
    {
        return view('poa-certifications.index', ['certifications' => PoaCertification::query()->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('poa-certifications.create', ['processes' => PoaProcess::query()->latest()->get(), 'units' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get(), 'activities' => PoaActivity::query()->where('status', 'APROBADA')->orderBy('number')->get()]);
    }

    public function store(StorePoaCertificationRequest $request, CreatePoaCertificationAction $create): RedirectResponse
    {
        $cert = $create->handle($request->validated(), $request->file('document'), $request->user());

        return redirect()->route('poa-certifications.show', $cert);
    }

    public function show(PoaCertification $poaCertification): View
    {
        return view('poa-certifications.show', ['certification' => $poaCertification->load(['details', 'documents']), 'statuses' => $poaCertification->status->allowedTransitions()]);
    }

    public function transition(TransitionPoaCertificationRequest $request, PoaCertification $poaCertification, TransitionPoaCertificationAction $transition): RedirectResponse
    {
        try {
            $transition->handle($poaCertification, CertificationStatus::from($request->validated('status')), $request->user(), $request->validated('comment'), $request->integer('version'));
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

return back()->with('status','Estado actualizado.');
    }
}
