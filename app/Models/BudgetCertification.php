<?php

namespace App\Models;

use App\Enums\BudgetCertificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetCertification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poa_process_id',
        'poa_certification_id',
        'certification_number',
        'certification_date',
        'year',
        'total_amount',
        'funding_source',
        'status',
        'concept',
        'created_by',
    ];

    protected $attributes = [
        'status' => 'ACTIVA',
    ];

    protected function casts(): array
    {
        return [
            'status' => BudgetCertificationStatus::class,
            'total_amount' => 'decimal:2',
            'certification_date' => 'date',
            'year' => 'integer',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(PoaProcess::class, 'poa_process_id');
    }

    public function poaCertification(): BelongsTo
    {
        return $this->belongsTo(PoaCertification::class, 'poa_certification_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BudgetCertificationLine::class);
    }

    public function accruals(): HasMany
    {
        return $this->hasMany(BudgetAccrual::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
