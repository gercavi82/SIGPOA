<?php

namespace App\Actions\Planning;

use App\Enums\ProcessStatus;
use App\Models\PoaProcess;
use App\Models\User;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransitionPoaProcessAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(private AuditService $auditService) {}

    public function handle(PoaProcess $process, ProcessStatus $target, User $actor, string $comment, int $version): PoaProcess
    {
        return DB::transaction(function () use ($process, $target, $comment, $version): PoaProcess {
            $locked = PoaProcess::query()->lockForUpdate()->findOrFail($process->id);
            if ($locked->version !== $version) {
                throw new DomainException('El proceso fue actualizado por otro usuario.');
            }
            if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw new DomainException('La transición solicitada no está permitida.');
            }
            $oldStatus = $locked->status->value;
            $locked->status = $target;
            $locked->version++;
            if ($target === ProcessStatus::Open) {
                $locked->opens_at ??= now();
            }
            if ($target === ProcessStatus::Closed) {
                $locked->closes_at = now();
            }
            $locked->save();
            $this->auditService->record('state_transition', $locked, 'planning', ['status' => $oldStatus], ['status' => $target->value], $comment);

            return $locked;
        });
    }
}
