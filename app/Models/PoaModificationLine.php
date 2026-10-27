<?php

namespace App\Models;

use Database\Factories\PoaModificationLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoaModificationLine extends Model
{
    /** @use HasFactory<PoaModificationLineFactory> */
    use HasFactory;

    protected $fillable = ['poa_modification_request_id', 'source_activity_id', 'destination_activity_id', 'debit_amount', 'credit_amount', 'description'];

    protected function casts(): array
    {
        return ['debit_amount' => 'decimal:2', 'credit_amount' => 'decimal:2'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PoaModificationRequest::class, 'poa_modification_request_id');
    }

    public function sourceActivity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class, 'source_activity_id');
    }

    public function destinationActivity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class, 'destination_activity_id');
    }

    public function months(): HasMany
    {
        return $this->hasMany(PoaModificationMonth::class);
    }
}
