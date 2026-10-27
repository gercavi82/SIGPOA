<?php

namespace App\Models;

use Database\Factories\ProgrammaticStructureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgrammaticStructure extends Model
{
    /** @use HasFactory<ProgrammaticStructureFactory> */
    use HasFactory;

    protected $fillable = ['parent_id', 'organizational_unit_id', 'year', 'type', 'code', 'name', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
