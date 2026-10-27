<?php

namespace App\Actions\Modifications;

use App\Enums\ModificationStatus;
use App\Models\PoaModificationRequest;
use App\Models\User;
use App\Models\WorkflowTransition;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransitionModificationAction
{
    public function __construct(private AuditService $audit) {}

    public function handle(PoaModificationRequest $request, ModificationStatus $target, User $actor, string $comment, int $version): PoaModificationRequest
    {
        return DB::transaction(function () use ($request, $target, $actor, $comment, $version) {
            $locked = PoaModificationRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->version !== $version) {
                throw new DomainException('La solicitud fue actualizada por otro usuario.');
            }if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw new DomainException('Transición no permitida.');
            }if ($target === ModificationStatus::Approved && $locked->requires_esigef && (! $locked->esigef_reform_number || ! $locked->reform_cur)) {
                throw new DomainException('Se requieren reforma eSIGEF y CUR registrados manualmente.');
            }$from = $locked->status;
            $locked->update(['status' => $target, 'version' => $locked->version + 1]);
            WorkflowTransition::query()->create(['transitionable_type' => $locked->getMorphClass(), 'transitionable_id' => $locked->id, 'from_status' => $from->value, 'to_status' => $target->value, 'user_id' => $actor->id, 'comment' => $comment]);
            $this->audit->record('state_transition', $locked, 'modifications', ['status' => $from->value], ['status' => $target->value], $comment);

            return $locked;
        });
    }
}
