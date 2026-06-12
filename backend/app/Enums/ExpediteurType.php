<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ExpediteurType: string
{
    use HasOptions;

    case UTILISATEUR = 'UTILISATEUR';
    case CHATBOT = 'CHATBOT';
}
