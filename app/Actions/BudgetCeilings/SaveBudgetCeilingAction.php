<?php

namespace App\Actions\BudgetCeilings;

use App\Models\BudgetCeiling;
use App\Models\EntityVersion;
use App\Models\User;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SaveBudgetCeilingAction
{
    public function __construct(private AuditService $auditService) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, User $actor): BudgetCeiling
    {
        return DB::transaction(function () use ($data, $actor): BudgetCeiling {
            $total = round((float) $data['current_expenditure'] + (float) $data['investment_expenditure'], 2);
            if (round(collect($data['units'])->sum('amount'), 2) > $total) {
                throw new DomainException('La distribución por unidades excede el techo institucional.');
            }
            $ceiling = BudgetCeiling::query()->create([...Arr::except($data, 'units'), 'total_amount' => $total, 'created_by' => $actor->id]);
            $ceiling->units()->createMany($data['units']);
            EntityVersion::query()->create(['versionable_type' => $ceiling->getMorphClass(), 'versionable_id' => $ceiling->id, 'version' => 1, 'snapshot' => $ceiling->load('units')->toArray(), 'created_by' => $actor->id]);
            $this->auditService->record('created', $ceiling, 'budget_ceilings', newValues: $ceiling->toArray());

            return $ceiling;
        });
    }
}
