<?php

namespace App\Models;

use App\Enums\BudgetCeilingStatus;
use Database\Factories\BudgetCeilingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCeiling extends Model
{
    /** @use HasFactory<BudgetCeilingFactory> */
    use HasFactory;

    protected $fillable = ['planning_period_id', 'source', 'current_expenditure', 'investment_expenditure', 'total_amount', 'methodology', 'status', 'created_by', 'version'];

    protected $attributes = ['status' => 'BORRADOR', 'version' => 1];

    protected function casts(): array
    {
        return ['status' => BudgetCeilingStatus::class, 'current_expenditure' => 'decimal:2', 'investment_expenditure' => 'decimal:2', 'total_amount' => 'decimal:2'];
    }

    public function planningPeriod(): BelongsTo
    {
        return $this->belongsTo(PlanningPeriod::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(BudgetCeilingUnit::class);
    }
}
