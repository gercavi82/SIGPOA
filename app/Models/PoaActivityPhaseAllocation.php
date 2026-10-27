<?php

namespace App\Models;

use Database\Factories\PoaActivityPhaseAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaActivityPhaseAllocation extends Model
{
    /** @use HasFactory<PoaActivityPhaseAllocationFactory> */
    use HasFactory;

    protected $fillable = ['poa_activity_id', 'poa_process_id', 'priority', 'adjusted_amount', 'adjusted_target', 'justification', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['adjusted_amount' => 'decimal:2', 'adjusted_target' => 'decimal:4'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class, 'poa_activity_id');
    }
}
