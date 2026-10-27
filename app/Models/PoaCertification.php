<?php

namespace App\Models;

use App\Enums\CertificationStatus;
use Database\Factories\PoaCertificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoaCertification extends Model
{
    /** @use HasFactory<PoaCertificationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['poa_process_id', 'organizational_unit_id', 'number', 'subject', 'status', 'total_amount', 'requested_by', 'version'];

    protected $attributes = ['status' => 'BORRADOR', 'version' => 1];

    protected function casts(): array
    {
        return ['status' => CertificationStatus::class, 'total_amount' => 'decimal:2'];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(PoaProcess::class, 'poa_process_id');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function budgetCertification(): HasOne
    {
        return $this->hasOne(BudgetCertification::class, 'poa_certification_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PoaCertificationDetail::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
