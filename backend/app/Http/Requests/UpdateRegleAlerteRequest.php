<?php

namespace App\Http\Requests;

use App\Enums\Severite;
use App\Enums\TypeAlerte;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateRegleAlerteRequest extends StoreRegleAlerteRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'required', 'string', 'max:120'],
            'metriqueCible' => ['sometimes', 'required', Rule::in(['cpu', 'ram', 'disque'])],
            'operateur' => ['sometimes', 'required', Rule::in(['>', '>=', '<', '<='])],
            'seuil' => ['sometimes', 'required', 'numeric', 'between:0,100'],
            'severite' => ['sometimes', 'required', new Enum(Severite::class)],
            'typeAlerte' => ['sometimes', 'required', new Enum(TypeAlerte::class)],
            'actif' => ['boolean'],
        ];
    }
}
