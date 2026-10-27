<?php

namespace App\Models;

use Database\Factories\SystemParameterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemParameter extends Model
{
    /** @use HasFactory<SystemParameterFactory> */
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description', 'is_public'];

    protected $attributes = ['type' => 'string', 'is_public' => false];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }
}
