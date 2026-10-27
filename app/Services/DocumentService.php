<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentService
{
    public function store(Model $entity, UploadedFile $file, string $type, User $uploader): Document
    {
        $checksum = hash_file('sha256', $file->getRealPath());
        $version = Document::query()->where('documentable_type', $entity->getMorphClass())->where('documentable_id', $entity->getKey())->where('document_type', $type)->max('version') + 1;
        $stored = Str::uuid().'.'.$file->extension();
        $path = $file->storeAs('documents/'.class_basename($entity).'/'.$entity->getKey(), $stored, 'local');

        return Document::query()->create(['documentable_type' => $entity->getMorphClass(), 'documentable_id' => $entity->getKey(), 'document_type' => $type, 'original_name' => $file->getClientOriginalName(), 'stored_name' => $stored, 'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize(), 'checksum' => $checksum, 'storage_disk' => 'local', 'storage_path' => $path, 'version' => $version, 'uploaded_by' => $uploader->id, 'uploaded_at' => now(), 'status' => 'ACTIVO']);
    }
}
