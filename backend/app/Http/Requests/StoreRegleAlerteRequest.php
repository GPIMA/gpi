<?php

namespace App\Http\Requests;

use App\Enums\Severite;
use App\Enums\TypeAlerte;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreRegleAlerteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route gated to administrators.
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:120'],
            'metriqueCible' => ['required', Rule::in(['cpu', 'ram', 'disque'])],
            'operateur' => ['required', Rule::in(['>', '>=', '<', '<='])],
            'seuil' => ['required', 'numeric', 'between:0,100'],
            'severite' => ['required', new Enum(Severite::class)],
            'typeAlerte' => ['required', new Enum(TypeAlerte::class)],
            'actif' => ['boolean'],
        ];
    }

    public function donnees(): array
    {
        $map = [
            'nom' => 'nom',
            'metriqueCible' => 'metrique_cible',
            'operateur' => 'operateur',
            'seuil' => 'seuil',
            'severite' => 'severite',
            'typeAlerte' => 'type_alerte',
            'actif' => 'actif',
        ];

        $validated = $this->validated();
        $data = [];
        foreach ($map as $input => $column) {
            if (array_key_exists($input, $validated)) {
                $data[$column] = $validated[$input];
            }
        }

        return $data;
    }
}
