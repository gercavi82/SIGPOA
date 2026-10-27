<?php

namespace App\Actions\Modifications;

use App\Enums\ModificationType;
use App\Models\PoaModificationRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentService;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateModificationAction
{
    public function __construct(
        private AuditService $audit,
        private ?DocumentService $documents = null
    ) {
        $this->documents ??= app(DocumentService::class);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, User $actor, ?UploadedFile $file = null): PoaModificationRequest
    {
        return DB::transaction(function () use ($data, $actor, $file): PoaModificationRequest {
            $linesData = $data['lines'] ?? [];
            $debits = collect($linesData)->sum('debit_amount');
            $credits = collect($linesData)->sum('credit_amount');
            $type = ModificationType::from($data['type']);

            if (in_array($type, [ModificationType::WithoutBudgetImpact, ModificationType::Rescheduling], true) && round($debits, 2) !== round($credits, 2)) {
                throw new DomainException('Los débitos y créditos deben estar balanceados.');
            }

            $request = PoaModificationRequest::query()->create([
                ...Arr::except($data, ['lines', 'document', 'document_type']),
                'requires_esigef' => in_array($type, [ModificationType::WithBudgetImpact, ModificationType::ResourceAllocation], true),
                'requested_by' => $actor->id,
            ]);

            foreach ($linesData as $lineData) {
                $monthsData = $lineData['months'] ?? [];
                $line = $request->lines()->create(Arr::except($lineData, 'months'));

                if (! empty($monthsData)) {
                    $line->months()->createMany($monthsData);
                }
            }

            if ($file !== null && ! empty($data['document_type'])) {
                $this->documents->store($request, $file, (string) $data['document_type'], $actor);
            }

            $this->audit->record('created', $request, 'modifications', newValues: $request->toArray());

            return $request;
        });
    }
}
