<?php

namespace App\Enums;

enum ImportBatchRowStatus: string
{
    case VALID = 'VALIDO';
    case INVALID = 'INVALIDO';
    case MATCHED = 'CONCILIADO';
    case UNMATCHED = 'NO_CONCILIADO';
    case PROCESSED = 'PROCESADO';
    case IGNORED = 'IGNORADO';
}
