<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Database\Factories\PoaActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoaActivity extends Model
{
    /** @use HasFactory<PoaActivityFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['poa_process_id', 'organizational_unit_id', 'programmatic_structure_id', 'number', 'description', 'objective', 'priority', 'status', 'unit_of_measure', 'annual_target', 'annual_amount', 'responsible_user_id', 'created_by', 'version'];

    protected $attributes = ['status' => 'BORRADOR', 'annual_target' => 0, 'annual_amount' => 0, 'version' => 1];

    protected function casts(): array
    {
        return ['status' => ActivityStatus::class, 'annual_target' => 'decimal:4', 'annual_amount' => 'decimal:2'];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(PoaProcess::class, 'poa_process_id');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function months(): HasMany
    {
        return $this->hasMany(PoaActivityMonth::class);
    }
}
