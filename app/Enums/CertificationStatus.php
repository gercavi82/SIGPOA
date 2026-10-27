<?php

namespace App\Enums;

enum CertificationStatus: string
{
    case Draft = 'BORRADOR';
    case SentArea = 'ENVIADA_AREA';
    case ReturnedArea = 'DEVUELTA_AREA';
    case ApprovedArea = 'APROBADA_AREA';
    case ReviewDpge = 'EN_REVISION_DPGE';
    case ReturnedDpge = 'DEVUELTA_DPGE';
    case Denied = 'NEGADA';
    case Preapproved = 'PREAPROBADA';
    case Approved = 'APROBADA';
    case Finalized = 'FINALIZADA';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft,self::ReturnedArea,self::ReturnedDpge => [self::SentArea], self::SentArea => [self::ReturnedArea, self::ApprovedArea], self::ApprovedArea => [self::ReviewDpge], self::ReviewDpge => [self::ReturnedDpge, self::Denied, self::Preapproved], self::Preapproved => [self::ReturnedDpge, self::Approved], self::Approved => [self::Finalized], self::Denied,self::Finalized => []
        };
    }
}
