<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TypeEquipement: string
{
    use HasOptions;

    case PC = 'PC';
    case SERVEUR = 'SERVEUR';
    case IMPRIMANTE = 'IMPRIMANTE';
    case ROUTEUR = 'ROUTEUR';
    case SWITCH = 'SWITCH';
    case SOURIS = 'SOURIS';
    case CLAVIER = 'CLAVIER';
    case ECRAN = 'ECRAN';
    case SOCLE = 'SOCLE';
}
