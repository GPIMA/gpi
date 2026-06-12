<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EtatAlerte: string
{
    use HasOptions;

    case ACTIVE = 'ACTIVE';
    case EN_COURS = 'EN_COURS';
    case RESOLUE = 'RESOLUE';
}
