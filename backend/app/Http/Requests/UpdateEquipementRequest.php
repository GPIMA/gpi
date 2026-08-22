<?php

namespace App\Http\Requests;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Partial update — every field is optional, but those provided are validated
 * with the same rules as creation. Unlike the creation form, fields may still
 * be cleared back to null here (e.g. unassigning an employee).
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
            'numeroSerie' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9-]{3,}$/'],
            'adresseIP' => ['nullable', 'ip'],
            'adresseMAC' => ['nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/'],
            'etat' => ['sometimes', 'required', new Enum(EtatEquipement::class)],
            'localisation' => ['nullable', 'string', Rule::in(self::SITES)],
            'dateAcquisition' => ['nullable', 'date'],
            'employeId' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // No defaulting on update — keep the payload as sent.
    }
}