<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route gated to administrators.
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:120'],
            'prenom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'role' => ['required', new Enum(RoleUtilisateur::class)],
            'telephone' => ['nullable', 'string', 'max:40'],
            'specialite' => ['nullable', 'string', 'max:120'],
            'departement' => ['nullable', 'string', 'max:120'],
        ];
    }
}
