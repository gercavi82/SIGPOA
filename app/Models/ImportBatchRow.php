<?php

namespace App\Models;

use App\Enums\ImportBatchRowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ImportBatchRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'row_number',
        'cur_number',
        'cur_line',
        'certification_number',
        'activity_code',
        'poa_activity_id',
        'budget_certification_id',
        'accrual_date',
        'amount',
        'status',
        'error_message',
        'raw_payload',
    ];

    protected $attributes = [
        'cur_line' => 1,
        'status' => 'VALIDO',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportBatchRowStatus::class,
            'row_number' => 'integer',
            'cur_line' => 'integer',
            'amount' => 'decimal:2',
            'accrual_date' => 'date',
            'raw_payload' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function poaActivity(): BelongsTo
    {
        return $this->belongsTo(PoaActivity::class);
    }

    public function budgetCertification(): BelongsTo
    {
        return $this->belongsTo(BudgetCertification::class);
    }

    public function accrual(): HasOne
    {
        return $this->hasOne(BudgetAccrual::class);
    }
}
