<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case Draft = 'BORRADOR';
    case Submitted = 'ENVIADA_AREA';
    case ReturnedArea = 'DEVUELTA_AREA';
    case ApprovedArea = 'APROBADA_AREA';
    case ReturnedDpge = 'DEVUELTA_DPGE';
    case Approved = 'APROBADA';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft, self::ReturnedArea, self::ReturnedDpge => [self::Submitted],
            self::Submitted => [self::ReturnedArea, self::ApprovedArea],
            self::ApprovedArea => [self::ReturnedDpge, self::Approved],
            self::Approved => [],
        };
    }
}
