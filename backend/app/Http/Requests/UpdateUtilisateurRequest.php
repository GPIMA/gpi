<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('utilisateur')->id;

        return [
            'nom' => ['sometimes', 'required', 'string', 'max:120', 'regex:/^[^0-9]+$/'],
            'prenom' => ['sometimes', 'required', 'string', 'max:120', 'regex:/^[^0-9]+$/'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['sometimes', 'required', new Enum(RoleUtilisateur::class)],
            'telephone' => ['sometimes', 'required', 'string', 'regex:/^\d{10}$/'],
            'departement' => ['nullable', 'string', Rule::in(StoreUtilisateurRequest::DEPARTEMENTS)],
            'localisation' => ['nullable', 'string', Rule::in(StoreUtilisateurRequest::SITES)],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.regex' => 'Le nom ne doit pas contenir de chiffres.',
            'prenom.regex' => 'Le prénom ne doit pas contenir de chiffres.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.mixed' => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial.',
        ];
    }
}