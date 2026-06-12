<?php

namespace App\Http\Requests;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreEquipementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route is already gated to administrators.
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:120'],
            'type' => ['required', new Enum(TypeEquipement::class)],
            'marque' => ['nullable', 'string', 'max:120'],
            'modele' => ['nullable', 'string', 'max:120'],
            'adresseIP' => ['nullable', 'ip'],
            'adresseMAC' => ['nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/'],
            'etat' => ['required', new Enum(EtatEquipement::class)],
            'localisation' => ['nullable', 'string', 'max:160'],
            'dateAcquisition' => ['nullable', 'date'],
        ];
    }

    /**
     * Map the API's camelCase payload to the model's snake_case columns.
     * Only the keys actually present are returned, so the same method serves
     * full creates and partial updates.
     */
    public function donnees(): array
    {
        $map = [
            'nom' => 'nom',
            'type' => 'type',
            'marque' => 'marque',
            'modele' => 'modele',
            'adresseIP' => 'adresse_ip',
            'adresseMAC' => 'adresse_mac',
            'etat' => 'etat',
            'localisation' => 'localisation',
            'dateAcquisition' => 'date_acquisition',
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

    protected function prepareForValidation(): void
    {
        // Default a new asset to offline when no state is supplied.
        if (! $this->has('etat')) {
            $this->merge(['etat' => EtatEquipement::HORS_LIGNE->value]);
        }
    }
}
