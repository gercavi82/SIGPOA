<?php

namespace App\Models;

use Database\Factories\PoaCertificationUpdateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoaCertificationUpdate extends Model
{
    /** @use HasFactory<PoaCertificationUpdateFactory> */
    use HasFactory;

    protected $fillable = ['poa_certification_id', 'type', 'description', 'changes', 'created_by'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }
}
