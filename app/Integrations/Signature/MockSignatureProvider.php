<?php

namespace App\Integrations\Signature;

use App\Contracts\SignatureProviderInterface;
use App\Models\Document;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Support\Str;

class MockSignatureProvider implements SignatureProviderInterface
{
    public function sign(Document $document, User $signer): Signature
    {
        return Signature::query()->create(['document_id' => $document->id, 'signer_id' => $signer->id, 'signed_at' => now(), 'result' => 'SIGNED_TEST', 'hash_before' => $document->checksum, 'hash_after' => hash('sha256', $document->checksum.$signer->id), 'provider' => 'mock', 'transaction_id' => (string) Str::uuid()]);
    }
}
