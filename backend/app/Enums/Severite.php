<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Severite: string
{
    use HasOptions;

    case FAIBLE = 'FAIBLE';
    case MOYENNE = 'MOYENNE';
    case ELEVEE = 'ELEVEE';
    case CRITIQUE = 'CRITIQUE';

    /** Ordinal weight, used to rank and sort alerts/incidents by urgency. */
    public function poids(): int
    {
        return match ($this) {
            self::FAIBLE => 1,
            self::MOYENNE => 2,
            self::ELEVEE => 3,
            self::CRITIQUE => 4,
        };
    }
}
