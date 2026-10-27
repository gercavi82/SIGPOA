<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetCertificationLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_certification_id',
        'line_number',
        'concept',
        'poa_activity_id',
        'funding_source',
        'amount',
        'status',
    ];

    protected $attributes = [
        'status' => 'ACTIVA',
    ];

    protected function casts(): array
    {
        return [
            'line_number' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function budgetCertification(): BelongsTo
    {
        return $this->belongsTo(BudgetCertification::class);
    }

    public function poaActivity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class);
    }
}
