<?php

namespace App\Actions\Planning;

use App\Enums\BudgetCeilingStatus;
use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use App\Models\BudgetCeiling;
use App\Models\PoaActivity;
use App\Models\PoaActivityPhaseAllocation;
use App\Models\PoaProcess;
use App\Models\User;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Facades\DB;

class AllocateActivityToPhaseAction
{
    public function __construct(private AuditService $auditService) {}

    /** @param array<string, mixed> $data */
    public function handle(PoaActivity $activity, array $data, User $actor): PoaActivityPhaseAllocation
    {
        return DB::transaction(function () use ($activity, $data, $actor): PoaActivityPhaseAllocation {
            $process = PoaProcess::query()->lockForUpdate()->findOrFail($data['poa_process_id']);
            if (! in_array($process->phase, [PoaPhase::Proforma, PoaPhase::PoaInicial], true) || $process->status !== ProcessStatus::Open) {
                throw new DomainException('La fase de destino no está abierta.');
            }
            $ceiling = BudgetCeiling::query()->where('planning_period_id', $process->planning_period_id)->where('status', BudgetCeilingStatus::Approved)->whereHas('units', fn ($query) => $query->where('organizational_unit_id', $activity->organizational_unit_id))->with(['units' => fn ($query) => $query->where('organizational_unit_id', $activity->organizational_unit_id)])->first();
            if (! $ceiling) {
                throw new DomainException('No existe un techo aprobado para la unidad y período.');
            }
            $used = PoaActivityPhaseAllocation::query()->where('poa_process_id', $process->id)->where('poa_activity_id', '!=', $activity->id)->whereHas('activity', fn ($query) => $query->where('organizational_unit_id', $activity->organizational_unit_id))->sum('adjusted_amount');
            if ($used + (float) $data['adjusted_amount'] > (float) $ceiling->units->first()->amount) {
                throw new DomainException('La asignación excede el techo aprobado de la unidad.');
            }
            $allocation = PoaActivityPhaseAllocation::query()->updateOrCreate(['poa_activity_id' => $activity->id, 'poa_process_id' => $process->id], [...$data, 'created_by' => $actor->id]);
            $this->auditService->record('phase_allocation', $allocation, 'planning', newValues: $allocation->toArray());

            return $allocation;
        });
    }
}
