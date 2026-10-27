<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    protected $fillable = ['observable_type', 'observable_id', 'user_id', 'body', 'context'];
}
