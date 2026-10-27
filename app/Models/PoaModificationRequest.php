<?php

namespace App\Models;

use App\Enums\ModificationStatus;
use App\Enums\ModificationType;
use Database\Factories\PoaModificationRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoaModificationRequest extends Model
{
    /** @use HasFactory<PoaModificationRequestFactory> */
    use HasFactory,SoftDeletes;

    protected $fillable = ['number', 'type', 'status', 'poa_process_id', 'organizational_unit_id', 'justification', 'requires_esigef', 'esigef_reform_number', 'reform_cur', 'requested_by', 'version'];

    protected $attributes = ['status' => 'BORRADOR', 'version' => 1, 'requires_esigef' => false];

    protected function casts(): array
    {
        return ['type' => ModificationType::class, 'status' => ModificationStatus::class, 'requires_esigef' => 'boolean'];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(PoaProcess::class, 'poa_process_id');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PoaModificationLine::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(PoaModificationResult::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function workflowTransitions(): MorphMany
    {
        return $this->morphMany(WorkflowTransition::class, 'transitionable');
    }

    public function observations(): MorphMany
    {
        return $this->morphMany(Observation::class, 'observable');
    }
}
