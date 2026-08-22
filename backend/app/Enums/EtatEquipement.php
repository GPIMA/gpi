<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EtatEquipement: string
{
    use HasOptions;

    case EN_LIGNE = 'EN_LIGNE';
    case HORS_LIGNE = 'HORS_LIGNE';
    case EN_PANNE = 'EN_PANNE';
    case EN_MAINTENANCE = 'EN_MAINTENANCE';
}
