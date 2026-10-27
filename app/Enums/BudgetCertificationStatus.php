<?php

namespace App\Enums;

enum BudgetCertificationStatus: string
{
    case ACTIVE = 'ACTIVA';
    case ANULLED = 'ANULADA';
    case LIQUIDATED = 'LIQUIDADA';
}
