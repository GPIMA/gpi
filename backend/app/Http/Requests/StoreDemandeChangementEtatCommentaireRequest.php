<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeChangementEtatCommentaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // L'accès à la demande est vérifié dans le contrôleur.
    }

    public function rules(): array
    {
        return [
            'contenu' => ['required', 'string', 'max:1000'],
        ];
    }
}
