<?php

namespace App\Actions\Certifications;

use App\Contracts\SignatureProviderInterface;
use App\Enums\CertificationStatus;
use App\Models\PoaCertification;
use App\Models\User;
use App\Models\WorkflowTransition;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransitionPoaCertificationAction
{
    public function __construct(private AuditService $audit, private SignatureProviderInterface $signatures) {}

    public function handle(PoaCertification $cert, CertificationStatus $target, User $actor, string $comment, int $version): PoaCertification
    {
        return DB::transaction(function () use ($cert, $target, $actor, $comment, $version): PoaCertification {
            $locked = PoaCertification::query()->with('documents')->lockForUpdate()->findOrFail($cert->id);
            if ($locked->version !== $version) {
                throw new DomainException('La certificación fue actualizada por otro usuario.');
            } if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw new DomainException('La transición no está permitida.');
            } if ($locked->documents->isEmpty()) {
                throw new DomainException('Se requiere al menos un documento habilitante.');
            } $from = $locked->status;
            $locked->update(['status' => $target, 'version' => $locked->version + 1]);
            WorkflowTransition::query()->create(['transitionable_type' => $locked->getMorphClass(), 'transitionable_id' => $locked->id, 'from_status' => $from->value, 'to_status' => $target->value, 'user_id' => $actor->id, 'comment' => $comment]);
            if (in_array($target, [CertificationStatus::ApprovedArea, CertificationStatus::Approved], true)) {
                $this->signatures->sign($locked->documents->sortByDesc('version')->first(), $actor);
            } $this->audit->record('state_transition', $locked, 'certifications', ['status' => $from->value], ['status' => $target->value], $comment);

            return $locked;
        });
    }
}
