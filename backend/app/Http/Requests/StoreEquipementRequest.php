<?php

namespace App\Http\Requests;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreEquipementRequest extends FormRequest
{
    /** The only sites equipment can be attached to. */
    public const SITES = ['Rabat', 'Casablanca', 'Tanger'];

    /** Peripherals with no network identity of their own — IP/MAC stay optional for these. */
    public const TYPES_SANS_RESEAU = ['SOURIS', 'CLAVIER', 'ECRAN', 'SOCLE'];

    public function authorize(): bool
    {
        return true; // Route is already gated to administrators.
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:120'],
            'type' => ['required', new Enum(TypeEquipement::class)],
            'marque' => ['required', 'string', 'max:120'],
            'modele' => ['required', 'string', 'max:120'],
            'numeroSerie' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9-]{3,}$/'],
            'adresseIP' => [
                'nullable', 'ip',
                Rule::requiredIf(fn () => ! in_array($this->input('type'), self::TYPES_SANS_RESEAU, true)),
            ],
            'adresseMAC' => [
                'nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/',
                Rule::requiredIf(fn () => ! in_array($this->input('type'), self::TYPES_SANS_RESEAU, true)),
            ],
            'etat' => ['required', new Enum(EtatEquipement::class)],
            'localisation' => ['required', 'string', Rule::in(self::SITES)],
            'dateAcquisition' => ['nullable', 'date'],
            'employeId' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Map the API's camelCase payload to the model's snake_case columns.
     * Only the keys actually present are returned, so the same method serves
     * full creates and partial updates. employeId is excluded here since it
     * is not a column on equipements — it is handled separately in the
     * controller to create/update an Affectation record.
     */
    public function donnees(): array
    {
        $map = [
            'nom' => 'nom',
            'type' => 'type',
            'marque' => 'marque',
            'modele' => 'modele',
            'numeroSerie' => 'numero_serie',
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