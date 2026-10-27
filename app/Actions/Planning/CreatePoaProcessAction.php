<?php

namespace App\Actions\Planning;

use App\Models\PoaProcess;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class CreatePoaProcessAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(private AuditService $auditService) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, User $actor): PoaProcess
    {
        return DB::transaction(function () use ($data, $actor): PoaProcess {
            $process = PoaProcess::query()->create([...$data, 'created_by' => $actor->id]);
            $this->auditService->record('created', $process, 'planning', newValues: $process->toArray());

            return $process;
        });
    }
}
