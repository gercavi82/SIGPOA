<?php

namespace App\Actions\Planning;

use App\Enums\ActivityStatus;
use App\Models\Observation;
use App\Models\PoaActivity;
use App\Models\User;
use App\Models\WorkflowTransition;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransitionPoaActivityAction
{
    public function __construct(private AuditService $auditService) {}

    public function handle(PoaActivity $activity, ActivityStatus $target, User $actor, string $comment, int $version): PoaActivity
    {
        return DB::transaction(function () use ($activity, $target, $actor, $comment, $version): PoaActivity {
            $locked = PoaActivity::query()->lockForUpdate()->findOrFail($activity->id);
            if ($locked->version !== $version) {
                throw new DomainException('La actividad fue actualizada por otro usuario.');
            }
            if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw new DomainException('La transición no está permitida.');
            }
            $from = $locked->status;
            $locked->update(['status' => $target, 'version' => $locked->version + 1]);
            WorkflowTransition::query()->create(['transitionable_type' => $locked->getMorphClass(), 'transitionable_id' => $locked->id, 'from_status' => $from->value, 'to_status' => $target->value, 'user_id' => $actor->id, 'comment' => $comment]);
            if (in_array($target, [ActivityStatus::ReturnedArea, ActivityStatus::ReturnedDpge], true)) {
                Observation::query()->create(['observable_type' => $locked->getMorphClass(), 'observable_id' => $locked->id, 'user_id' => $actor->id, 'body' => $comment, 'context' => $target->value]);
            }
            $this->auditService->record('state_transition', $locked, 'planning', ['status' => $from->value], ['status' => $target->value], $comment);

            return $locked;
        });
    }
}
