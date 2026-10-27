<?php

namespace App\Http\Controllers;

use App\Actions\Modifications\AttachModificationDocumentAction;
use App\Actions\Modifications\CreateModificationAction;
use App\Actions\Modifications\RecordEsigefReformAction;
use App\Actions\Modifications\RecordViabilityAction;
use App\Actions\Modifications\TransitionModificationAction;
use App\Actions\Modifications\UpdateModificationAction;
use App\Enums\ActivityStatus;
use App\Enums\ModificationStatus;
use App\Enums\ModificationType;
use App\Enums\ProcessStatus;
use App\Http\Requests\AttachModificationDocumentRequest;
use App\Http\Requests\RecordEsigefReformRequest;
use App\Http\Requests\RecordViabilityRequest;
use App\Http\Requests\StorePoaModificationRequest;
use App\Http\Requests\TransitionPoaModificationRequest;
use App\Models\OrganizationalUnit;
use App\Models\PoaActivity;
use App\Models\PoaModificationRequest;
use App\Models\PoaProcess;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PoaModificationRequestController extends Controller
{
    public function index(): View
    {
        return view('modifications.index', [
            'requests' => PoaModificationRequest::query()
                ->with(['process.planningPeriod', 'organizationalUnit', 'requester'])
                ->withCount('lines')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('modifications.create', [
            'processes' => PoaProcess::query()->where('status', ProcessStatus::Open)->with('planningPeriod')->latest()->get(),
            'units' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get(),
            'activities' => PoaActivity::query()->where('status', ActivityStatus::Approved)->orderBy('number')->get(),
            'types' => ModificationType::cases(),
        ]);
    }

    public function store(StorePoaModificationRequest $request, CreateModificationAction $create): RedirectResponse
    {
        try {
            $modification = $create->handle(
                $request->validated(),
                $request->user(),
                $request->file('document')
            );
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['modification' => $exception->getMessage()]);
        }

        return redirect()->route('modifications.show', $modification)->with('status', 'Solicitud de modificación creada con éxito.');
    }

    public function show(PoaModificationRequest $poaModificationRequest): View
    {
        return view('modifications.show', [
            'request' => $poaModificationRequest->load([
                'process.planningPeriod',
                'organizationalUnit',
                'requester',
                'lines.sourceActivity',
                'lines.destinationActivity',
                'lines.months',
                'result.approver',
                'documents.uploader',
                'workflowTransitions.user',
                'observations.user',
            ]),
            'statuses' => $poaModificationRequest->status->allowedTransitions(),
        ]);
    }

    public function edit(PoaModificationRequest $poaModificationRequest): View
    {
        abort_unless(
            in_array($poaModificationRequest->status, [ModificationStatus::Draft, ModificationStatus::Returned], true),
            403,
            'La solicitud no está en un estado editable.'
        );

        return view('modifications.edit', [
            'request' => $poaModificationRequest->load(['lines.months', 'documents']),
            'processes' => PoaProcess::query()->where('status', ProcessStatus::Open)->with('planningPeriod')->latest()->get(),
            'units' => OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get(),
            'activities' => PoaActivity::query()->where('status', ActivityStatus::Approved)->orderBy('number')->get(),
            'types' => ModificationType::cases(),
        ]);
    }

    public function update(StorePoaModificationRequest $request, PoaModificationRequest $poaModificationRequest, UpdateModificationAction $update): RedirectResponse
    {
        try {
            $update->handle($poaModificationRequest, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['modification' => $exception->getMessage()]);
        }

        return redirect()->route('modifications.show', $poaModificationRequest)->with('status', 'Solicitud de modificación corregida y versionada.');
    }

    public function transition(TransitionPoaModificationRequest $request, PoaModificationRequest $poaModificationRequest, TransitionModificationAction $transition): RedirectResponse
    {
        try {
            $transition->handle(
                $poaModificationRequest,
                ModificationStatus::from($request->validated('status')),
                $request->user(),
                $request->validated('comment'),
                $request->integer('version')
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Transición de estado ejecutada.');
    }

    public function recordViability(RecordViabilityRequest $request, PoaModificationRequest $poaModificationRequest, RecordViabilityAction $action): RedirectResponse
    {
        try {
            $action->handle($poaModificationRequest, $request->validated('viability_report'), $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['viability' => $exception->getMessage()]);
        }

        return back()->with('status', 'Informe de viabilidad registrado.');
    }

    public function recordEsigef(RecordEsigefReformRequest $request, PoaModificationRequest $poaModificationRequest, RecordEsigefReformAction $action): RedirectResponse
    {
        try {
            $action->handle(
                $poaModificationRequest,
                $request->validated('esigef_reform_number'),
                $request->validated('reform_cur'),
                $request->user()
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['esigef' => $exception->getMessage()]);
        }

        return back()->with('status', 'Reforma eSIGEF y CUR registrados.');
    }

    public function attachDocument(AttachModificationDocumentRequest $request, PoaModificationRequest $poaModificationRequest, AttachModificationDocumentAction $action): RedirectResponse
    {
        try {
            $action->handle(
                $poaModificationRequest,
                $request->file('document'),
                $request->validated('document_type'),
                $request->user()
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['document' => $exception->getMessage()]);
        }

        return back()->with('status', 'Documento habilitante adjuntado con éxito.');
    }
}
