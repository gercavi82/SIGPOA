<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = ['document_id', 'signer_id', 'signed_at', 'result', 'hash_before', 'hash_after', 'provider', 'transaction_id'];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }
}
