<?php

namespace App\Enums;

enum ModificationType: string
{
    case WithoutBudgetImpact = 'SIN_AFECTACION';
    case WithBudgetImpact = 'CON_AFECTACION';
    case ResourceAllocation = 'ASIGNACION_RECURSOS';
    case Rescheduling = 'REPROGRAMACION';
}
