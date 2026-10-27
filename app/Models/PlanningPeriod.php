<?php

namespace App\Models;

use Database\Factories\PlanningPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningPeriod extends Model
{
    /** @use HasFactory<PlanningPeriodFactory> */
    use HasFactory;

    protected $fillable = ['year', 'starts_at', 'ends_at', 'is_active'];

    protected $attributes = ['is_active' => false];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'ends_at' => 'date', 'is_active' => 'boolean'];
    }

    public function poaProcesses(): HasMany
    {
        return $this->hasMany(PoaProcess::class);
    }
}
