<?php

namespace App\Models;

use App\Enums\ExecutionClosingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'status',
        'total_planned',
        'total_accrued',
        'notes',
        'closed_by',
        'closed_at',
    ];

    protected $attributes = [
        'status' => 'CERRADO',
        'total_planned' => 0,
        'total_accrued' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionClosingStatus::class,
            'year' => 'integer',
            'month' => 'integer',
            'total_planned' => 'decimal:2',
            'total_accrued' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
