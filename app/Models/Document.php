<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    protected $fillable = ['documentable_type', 'documentable_id', 'document_type', 'original_name', 'stored_name', 'mime_type', 'size', 'checksum', 'storage_disk', 'storage_path', 'version', 'uploaded_by', 'uploaded_at', 'status'];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
