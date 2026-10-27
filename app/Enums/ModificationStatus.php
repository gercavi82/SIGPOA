<?php

namespace App\Enums;

enum ModificationStatus: string
{
    case Draft = 'BORRADOR';
    case SentArea = 'ENVIADA_AREA';
    case BudgetReview = 'VERIFICACION_FINANCIERA';
    case DpgeReview = 'REVISION_DPGE';
    case Returned = 'DEVUELTA';
    case Denied = 'NEGADA';
    case Viable = 'VIABLE';
    case Authorized = 'AUTORIZADA';
    case ReformPending = 'REFORMA_PENDIENTE';
    case Approved = 'APROBADA';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft,self::Returned => [self::SentArea],self::SentArea => [self::Returned, self::BudgetReview],self::BudgetReview => [self::Returned, self::DpgeReview],self::DpgeReview => [self::Returned, self::Denied, self::Viable],self::Viable => [self::Authorized],self::Authorized => [self::ReformPending, self::Approved],self::ReformPending => [self::Approved, self::Returned],self::Denied,self::Approved => []
        };
    }
}
