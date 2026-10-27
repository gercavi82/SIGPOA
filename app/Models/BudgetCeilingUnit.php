<?php

namespace App\Models;

use Database\Factories\BudgetCeilingUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetCeilingUnit extends Model
{
    /** @use HasFactory<BudgetCeilingUnitFactory> */
    use HasFactory;

    protected $fillable = ['budget_ceiling_id', 'organizational_unit_id', 'amount', 'adjustment_reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }
}
