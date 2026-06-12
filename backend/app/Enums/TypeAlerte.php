<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TypeAlerte: string
{
    use HasOptions;

    case CPU_OVERLOAD = 'CPU_OVERLOAD';
    case RAM_OVERLOAD = 'RAM_OVERLOAD';
    case DISK_FULL = 'DISK_FULL';
    case DECONNEXION = 'DECONNEXION';
    case PANNE = 'PANNE';
}
