<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Discriminator for the abstract Utilisateur hierarchy of the class diagram
 * (Administrateur / Technicien / Employe), mapped onto a single users table.
 */
enum RoleUtilisateur: string
{
    use HasOptions;

    case ADMIN = 'ADMIN';
    case TECHNICIEN = 'TECHNICIEN';
    case EMPLOYE = 'EMPLOYE';
}
