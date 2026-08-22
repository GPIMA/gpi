<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Motif choisi par le technicien quand l'employé ramène son poste suite à une
 * demande de restitution.
 */
enum MotifRetourPoste: string
{
    use HasOptions;

    case MAINTENANCE_SUR_PLACE = 'MAINTENANCE_SUR_PLACE';
    case NOUVELLE_DATE = 'NOUVELLE_DATE';
    case POSTE_REMPLACE = 'POSTE_REMPLACE';
}
