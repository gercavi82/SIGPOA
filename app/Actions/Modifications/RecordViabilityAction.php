<?php

namespace App\Actions\Modifications;

use App\Models\PoaModificationRequest;
use App\Models\PoaModificationResult;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class RecordViabilityAction
{
    public function __construct(private AuditService $audit) {}

    public function handle(PoaModificationRequest $request, string $viabilityReport, User $actor): PoaModificationResult
    {
        return DB::transaction(function () use ($request, $viabilityReport, $actor): PoaModificationResult {
            $locked = PoaModificationRequest::query()->lockForUpdate()->findOrFail($request->id);

            $result = PoaModificationResult::query()->updateOrCreate(
                ['poa_modification_request_id' => $locked->id],
                [
                    'viability_report' => $viabilityReport,
                    'approved_at' => now(),
                    'approved_by' => $actor->id,
                ]
            );

            $this->audit->record('viability_recorded', $locked, 'modifications', null, ['viability_report' => $viabilityReport], 'Informe de viabilidad técnico emitido');

            return $result;
        });
    }
}
