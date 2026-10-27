<?php

namespace App\Models;

use Database\Factories\PoaCertificationDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoaCertificationDetail extends Model
{
    /** @use HasFactory<PoaCertificationDetailFactory> */
    use HasFactory;

    protected $fillable = ['poa_certification_id', 'poa_activity_id', 'description', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }
}
