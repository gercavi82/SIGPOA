<?php

namespace App\Models;

use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use Database\Factories\PoaProcessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaProcess extends Model
{
    /** @use HasFactory<PoaProcessFactory> */
    use HasFactory;

    protected $fillable = ['planning_period_id', 'phase', 'status', 'opens_at', 'closes_at', 'responsible_user_id', 'created_by', 'version'];

    protected $attributes = ['status' => 'BORRADOR', 'version' => 1];

    protected function casts(): array
    {
        return ['phase' => PoaPhase::class, 'status' => ProcessStatus::class, 'opens_at' => 'datetime', 'closes_at' => 'datetime'];
    }

    public function planningPeriod(): BelongsTo
    {
        return $this->belongsTo(PlanningPeriod::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
