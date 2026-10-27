<?php

namespace App\Actions\Modifications;

use App\Enums\ModificationStatus;
use App\Enums\ModificationType;
use App\Models\EntityVersion;
use App\Models\PoaModificationRequest;
use App\Models\User;
use App\Services\AuditService;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateModificationAction
{
    public function __construct(private AuditService $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(PoaModificationRequest $request, array $data, User $actor): PoaModificationRequest
    {
        return DB::transaction(function () use ($request, $data, $actor): PoaModificationRequest {
            $locked = PoaModificationRequest::query()->lockForUpdate()->findOrFail($request->id);

            if (! in_array($locked->status, [ModificationStatus::Draft, ModificationStatus::Returned], true)) {
                throw new DomainException('La solicitud no está en un estado editable.');
            }

            $version = (int) ($data['version'] ?? $locked->version);
            if ($locked->version !== $version) {
                throw new DomainException('La solicitud fue actualizada por otro usuario.');
            }

            $linesData = $data['lines'] ?? [];
            $debits = collect($linesData)->sum('debit_amount');
            $credits = collect($linesData)->sum('credit_amount');
            $type = ModificationType::from($data['type'] ?? $locked->type->value);

            if (in_array($type, [ModificationType::WithoutBudgetImpact, ModificationType::Rescheduling], true) && round($debits, 2) !== round($credits, 2)) {
                throw new DomainException('Los débitos y créditos deben estar balanceados.');
            }

            $oldValues = $locked->toArray();

            $locked->fill([
                ...Arr::except($data, ['lines', 'version', 'document', 'document_type']),
                'type' => $type,
                'requires_esigef' => in_array($type, [ModificationType::WithBudgetImpact, ModificationType::ResourceAllocation], true),
                'version' => $locked->version + 1,
            ]);
            $locked->save();

            $locked->lines()->delete();
            foreach ($linesData as $lineData) {
                $monthsData = $lineData['months'] ?? [];
                $line = $locked->lines()->create(Arr::except($lineData, 'months'));

                if (! empty($monthsData)) {
                    $line->months()->createMany($monthsData);
                }
            }

            EntityVersion::query()->create([
                'versionable_type' => $locked->getMorphClass(),
                'versionable_id' => $locked->id,
                'version' => $locked->version,
                'snapshot' => $locked->load(['lines.months', 'documents'])->toArray(),
                'created_by' => $actor->id,
            ]);

            $this->audit->record('updated', $locked, 'modifications', $oldValues, $locked->toArray());

            return $locked;
        });
    }
}
