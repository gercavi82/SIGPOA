<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_code',
        'original_filename',
        'file_hash',
        'file_path',
        'year',
        'month',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'unmatched_rows',
        'processed_rows',
        'error_log',
        'imported_by',
        'processed_by',
        'processed_at',
    ];

    protected $attributes = [
        'status' => 'PENDIENTE',
        'total_rows' => 0,
        'valid_rows' => 0,
        'invalid_rows' => 0,
        'unmatched_rows' => 0,
        'processed_rows' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportBatchStatus::class,
            'year' => 'integer',
            'month' => 'integer',
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
            'unmatched_rows' => 'integer',
            'processed_rows' => 'integer',
            'error_log' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportBatchRow::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function accruals(): HasMany
    {
        return $this->hasMany(BudgetAccrual::class);
    }
}
