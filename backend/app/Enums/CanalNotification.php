<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum CanalNotification: string
{
    use HasOptions;

    case EMAIL = 'EMAIL';
    case INTERFACE = 'INTERFACE';
    case SMS = 'SMS';
}
