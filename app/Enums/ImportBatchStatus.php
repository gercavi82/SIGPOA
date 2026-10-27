<?php

namespace App\Enums;

enum ImportBatchStatus: string
{
    case PENDING = 'PENDIENTE';
    case VALIDATED = 'VALIDADO';
    case PROCESSED = 'PROCESADO';
    case FAILED = 'FALLIDO';
    case PARTIAL = 'PARCIAL';
}
