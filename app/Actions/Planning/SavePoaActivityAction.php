<?php

namespace App\Actions\Planning;

use App\Enums\ActivityStatus;
use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use App\Models\EntityVersion;
use App\Models\PoaActivity;
use App\Models\PoaProcess;
use App\Models\User;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SavePoaActivityAction
{
    public function __construct(private AuditService $auditService) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, User $actor, ?PoaActivity $activity = null): PoaActivity
    {
        return DB::transaction(function () use ($data, $actor, $activity): PoaActivity {
            $process = PoaProcess::query()->findOrFail($data['poa_process_id']);
            if ($process->phase !== PoaPhase::PoaNecesidades || $process->status !== ProcessStatus::Open) {
                throw new DomainException('El proceso de necesidades no está abierto.');
            }
            $months = $data['months'] ?? [];
            if ($months !== [] && round(collect($months)->sum('amount'), 2) !== round((float) $data['annual_amount'], 2)) {
                throw new DomainException('La suma mensual debe coincidir con el monto anual.');
            }
            $activity ??= new PoaActivity(['created_by' => $actor->id]);
            if ($activity->exists && (! in_array($activity->status, [ActivityStatus::Draft, ActivityStatus::ReturnedArea, ActivityStatus::ReturnedDpge], true) || $activity->version !== (int) ($data['version'] ?? 0))) {
                throw new DomainException('La actividad no puede editarse o fue actualizada por otro usuario.');
            }
            $activity->fill(Arr::except($data, ['months', 'version']));
            if ($activity->exists) {
                $activity->version++;
            }
            $activity->save();
            $activity->months()->delete();
            $activity->months()->createMany($months);
            EntityVersion::query()->create(['versionable_type' => $activity->getMorphClass(), 'versionable_id' => $activity->id, 'version' => $activity->version, 'snapshot' => $activity->load('months')->toArray(), 'created_by' => $actor->id]);
            $this->auditService->record($activity->wasRecentlyCreated ? 'created' : 'updated', $activity, 'planning', newValues: $activity->toArray());

            return $activity;
        });
    }
}
