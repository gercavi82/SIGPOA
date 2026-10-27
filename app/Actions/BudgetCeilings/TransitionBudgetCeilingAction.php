<?php

namespace App\Actions\BudgetCeilings;

use App\Enums\BudgetCeilingStatus;
use App\Models\BudgetCeiling;
use App\Models\User;
use App\Models\WorkflowTransition;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransitionBudgetCeilingAction
{
    public function __construct(private AuditService $auditService) {}

    public function handle(BudgetCeiling $ceiling, BudgetCeilingStatus $target, User $actor, string $comment, int $version): BudgetCeiling
    {
        return DB::transaction(function () use ($ceiling, $target, $actor, $comment, $version): BudgetCeiling {
            $locked = BudgetCeiling::query()->with('units')->lockForUpdate()->findOrFail($ceiling->id);
            if ($locked->version !== $version) {
                throw new DomainException('El techo fue actualizado por otro usuario.');
            }
            if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw new DomainException('La transición no está permitida.');
            }
            if ($target === BudgetCeilingStatus::Approved && round((float) $locked->units->sum('amount'), 2) !== round((float) $locked->total_amount, 2)) {
                throw new DomainException('Para aprobar, el techo debe estar distribuido completamente.');
            }
            $from = $locked->status;
            $locked->update(['status' => $target, 'version' => $locked->version + 1]);
            WorkflowTransition::query()->create(['transitionable_type' => $locked->getMorphClass(), 'transitionable_id' => $locked->id, 'from_status' => $from->value, 'to_status' => $target->value, 'user_id' => $actor->id, 'comment' => $comment]);
            $this->auditService->record('state_transition', $locked, 'budget_ceilings', ['status' => $from->value], ['status' => $target->value], $comment);

            return $locked;
        });
    }
}
