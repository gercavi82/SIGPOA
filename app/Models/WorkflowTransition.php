<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowTransition extends Model
{
    protected $fillable = ['transitionable_type', 'transitionable_id', 'from_status', 'to_status', 'user_id', 'comment', 'context'];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
