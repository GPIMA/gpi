<?php

namespace App\Http\Requests;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use Illuminate\Validation\Rules\Enum;

/**
 * Partial update — every field is optional, but those provided are validated
 * with the same rules as creation.
 */
class UpdateEquipementRequest extends StoreEquipementRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'required', 'string', 'max:120'],
            'type' => ['sometimes', 'required', new Enum(TypeEquipement::class)],
            'marque' => ['nullable', 'string', 'max:120'],
            'modele' => ['nullable', 'string', 'max:120'],
            'adresseIP' => ['nullable', 'ip'],
            'adresseMAC' => ['nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/'],
            'etat' => ['sometimes', 'required', new Enum(EtatEquipement::class)],
            'localisation' => ['nullable', 'string', 'max:160'],
            'dateAcquisition' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // No defaulting on update — keep the payload as sent.
    }
}
