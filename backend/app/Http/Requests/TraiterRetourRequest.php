<?php

namespace App\Http\Requests;

use App\Enums\MotifRetourPoste;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Traitement du retour du poste par le technicien, une fois que l'employé l'a
 * ramené suite à une demande de restitution. Les règles varient selon le
 * motif choisi parmi les 3 possibles.
 */
class TraiterRetourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motif' => ['required', new Enum(MotifRetourPoste::class)],
            'commentaire' => ['nullable', 'string', 'max:1000'],
            // Petite marge (2 min) pour absorber l'arrondi du champ datetime-local
            // côté client et la latence réseau (voir DemanderRestitutionRequest).
            'dateRestitution' => ['required_if:motif,NOUVELLE_DATE', 'nullable', 'date', 'after_or_equal:' . now()->subMinutes(2)],
            'nouvelEquipementId' => ['required_if:motif,POSTE_REMPLACE', 'nullable', 'integer', 'exists:equipements,id'],
            // Motif "Nouvelle date" uniquement : poste remplaçant temporaire,
            // affecté à l'employé en attendant la réparation de son poste.
            // Optionnel — l'employé peut ne pas en avoir besoin.
            'nouvelEquipementRemplacementId' => ['nullable', 'integer', 'exists:equipements,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'dateRestitution.required_if' => 'La nouvelle date de restitution est obligatoire.',
            'nouvelEquipementId.required_if' => 'Le nouveau poste à attribuer est obligatoire.',
        ];
    }
}
