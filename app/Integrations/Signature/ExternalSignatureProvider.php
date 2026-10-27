<?php

namespace App\Integrations\Signature;

use App\Contracts\SignatureProviderInterface;
use App\Models\Document;
use App\Models\Signature;
use App\Models\User;
use DomainException;

class ExternalSignatureProvider implements SignatureProviderInterface
{
    public function sign(Document $document, User $signer): Signature
    {
        throw new DomainException('Proveedor institucional de firma pendiente de configuración.');
    }
}
