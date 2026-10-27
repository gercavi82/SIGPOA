<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetAccrual extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cur_number',
        'cur_line',
        'year',
        'month',
        'accrual_date',
        'accrued_amount',
        'poa_activity_id',
        'budget_certification_id',
        'import_batch_id',
        'import_batch_row_id',
        'observations',
        'status',
        'created_by',
    ];

    protected $attributes = [
        'cur_line' => 1,
        'status' => 'ACTIVO',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'cur_line' => 'integer',
            'accrual_date' => 'date',
            'accrued_amount' => 'decimal:2',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class, 'poa_activity_id');
    }

    public function budgetCertification(): BelongsTo
    {
        return $this->belongsTo(BudgetCertification::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function batchRow(): BelongsTo
    {
        return $this->belongsTo(ImportBatchRow::class, 'import_batch_row_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
