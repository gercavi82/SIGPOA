<?php

namespace App\Enums;

enum ProcessStatus: string
{
    case Draft = 'BORRADOR';
    case Open = 'ABIERTO';
    case Closed = 'CERRADO';
    case Cancelled = 'CANCELADO';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Open, self::Cancelled],
            self::Open => [self::Closed],
            self::Closed, self::Cancelled => [],
        };
    }
}
