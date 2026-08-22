<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Cycle de vie d'une demande de changement d'état soumise par un technicien
 * (quand le changement n'est pas déclenché par la résolution d'un incident).
 */
enum StatutDemandeChangementEtat: string
{
    use HasOptions;

    case EN_ATTENTE = 'EN_ATTENTE';
    case APPROUVEE = 'APPROUVEE';
    case REJETEE = 'REJETEE';
}
