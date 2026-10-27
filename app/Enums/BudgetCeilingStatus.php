<?php

namespace App\Enums;

enum BudgetCeilingStatus: string
{
    case Draft = 'BORRADOR';
    case UnderAnalysis = 'EN_ANALISIS';
    case Approved = 'APROBADO';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::UnderAnalysis], self::UnderAnalysis => [self::Draft, self::Approved], self::Approved => []
        };
    }
}
