<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case TECHNICIEN = 'TECHNICIEN';
    case EMPLOYE = 'EMPLOYE';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::TECHNICIEN => 'Technicien',
            self::EMPLOYE => 'Employé',
        };
    }

    /** Liste des options [value, label] pour les selects / l'endpoint /enums. */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}