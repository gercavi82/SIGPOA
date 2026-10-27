<?php

namespace App\Models;

use Database\Factories\ControlLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlLine extends Model
{
    /** @use HasFactory<ControlLineFactory> */
    use HasFactory;

    protected $fillable = ['poa_process_id', 'code', 'name', 'weight', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['weight' => 'decimal:4', 'is_active' => 'boolean'];
    }
}
