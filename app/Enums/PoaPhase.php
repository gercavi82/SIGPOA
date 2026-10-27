<?php

namespace App\Enums;

enum PoaPhase: string
{
    case PoaNecesidades = 'POA_NECESIDADES';
    case AjusteTechos = 'AJUSTE_TECHOS';
    case Proforma = 'PROFORMA';
    case PoaInicial = 'POA_INICIAL';
    case Certificacion = 'CERTIFICACION';
    case Modificacion = 'MODIFICACION';
    case Ejecucion = 'EJECUCION';
    case Cierre = 'CIERRE';
}
