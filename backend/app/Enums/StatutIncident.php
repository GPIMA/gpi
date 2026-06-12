<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum StatutIncident: string
{
    use HasOptions;

    case OUVERT = 'OUVERT';
    case EN_COURS = 'EN_COURS';
    case RESOLU = 'RESOLU';
    case FERME = 'FERME';
}
