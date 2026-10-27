<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaModificationResult extends Model
{
    protected $fillable = ['poa_modification_request_id', 'viability_report', 'approved_at', 'approved_by'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PoaModificationRequest::class, 'poa_modification_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
