<?php

namespace App\Actions\Modifications;

use App\Models\Document;
use App\Models\PoaModificationRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AttachModificationDocumentAction
{
    public function __construct(
        private DocumentService $documents,
        private AuditService $audit
    ) {}

    public function handle(PoaModificationRequest $request, UploadedFile $file, string $type, User $actor): Document
    {
        return DB::transaction(function () use ($request, $file, $type, $actor): Document {
            $doc = $this->documents->store($request, $file, $type, $actor);
            $this->audit->record('document_attached', $request, 'modifications', null, ['document_id' => $doc->id, 'type' => $type], 'Documento habilitante adjuntado');

            return $doc;
        });
    }
}
