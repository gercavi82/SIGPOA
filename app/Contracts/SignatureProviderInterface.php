<?php

namespace App\Contracts;

use App\Models\Document;
use App\Models\Signature;
use App\Models\User;

interface SignatureProviderInterface
{
    public function sign(Document $document, User $signer): Signature;
}
