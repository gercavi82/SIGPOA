<?php

namespace App\Actions\Certifications;

use App\Models\PoaCertification;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreatePoaCertificationAction
{
    public function __construct(private DocumentService $documents, private AuditService $audit) {}

    /** @param array<string,mixed> $data */
    public function handle(array $data, UploadedFile $file, User $actor): PoaCertification
    {
        return DB::transaction(function () use ($data, $file, $actor): PoaCertification {
            $total = collect($data['details'])->sum('amount');
            $cert = PoaCertification::query()->create([...Arr::except($data, ['details', 'document', 'document_type']), 'total_amount' => $total, 'requested_by' => $actor->id]);
            $cert->details()->createMany($data['details']);
            $this->documents->store($cert, $file, $data['document_type'], $actor);
            $this->audit->record('created', $cert, 'certifications', newValues: $cert->toArray());

            return $cert;
        });
    }
}
