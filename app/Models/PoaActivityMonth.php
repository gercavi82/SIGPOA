<?php

namespace App\Models;

use Database\Factories\PoaActivityMonthFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaActivityMonth extends Model
{
    /** @use HasFactory<PoaActivityMonthFactory> */
    use HasFactory;

    protected $fillable = ['poa_activity_id', 'month', 'target', 'amount'];

    protected function casts(): array
    {
        return ['target' => 'decimal:4', 'amount' => 'decimal:2'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class, 'poa_activity_id');
    }
}
