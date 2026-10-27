<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaModificationMonth extends Model
{
    protected $fillable = ['poa_modification_line_id', 'month', 'debit_amount', 'credit_amount'];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'debit_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
        ];
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(PoaModificationLine::class, 'poa_modification_line_id');
    }
}
