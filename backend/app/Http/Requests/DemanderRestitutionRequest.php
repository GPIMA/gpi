<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DemanderRestitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Petite marge (2 min) pour absorber l'arrondi du champ datetime-local
            // côté client et la latence réseau, sans quoi une date pré-remplie sur
            // "maintenant" peut être rejetée au moment de la soumission.
            'dateRestitution' => ['required', 'date', 'after_or_equal:' . now()->subMinutes(2)],
        ];
    }
}