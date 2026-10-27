<?php

namespace App\Actions\Modifications;

use App\Models\PoaModificationRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class RecordEsigefReformAction
{
    public function __construct(private AuditService $audit) {}

    public function handle(PoaModificationRequest $request, string $reformNumber, string $reformCur, User $actor): PoaModificationRequest
    {
        return DB::transaction(function () use ($request, $reformNumber, $reformCur): PoaModificationRequest {
            $locked = PoaModificationRequest::query()->lockForUpdate()->findOrFail($request->id);

            $old = [
                'esigef_reform_number' => $locked->esigef_reform_number,
                'reform_cur' => $locked->reform_cur,
            ];

            $locked->update([
                'esigef_reform_number' => $reformNumber,
                'reform_cur' => $reformCur,
            ]);

            $this->audit->record(
                'esigef_reform_recorded',
                $locked,
                'modifications',
                $old,
                ['esigef_reform_number' => $reformNumber, 'reform_cur' => $reformCur],
                'Registro manual de reforma eSIGEF y CUR'
            );

            return $locked;
        });
    }
}
