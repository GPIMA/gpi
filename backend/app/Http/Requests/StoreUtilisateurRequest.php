<?php

namespace App\Http\Requests;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreUtilisateurRequest extends FormRequest
{
    /** The only sites a user can be attached to. */
    public const SITES = ['Rabat', 'Casablanca', 'Tanger'];

    /** The only departments an employee can belong to. */
    public const DEPARTEMENTS = ['RH', 'PROD', 'Direction'];

    public function authorize(): bool
    {
        return true; // Route gated to administrators.
    }

    public function rules(): array
    {
        return [
          'nom' => ['required', 'string', 'max:120', 'regex:/^[^0-9]+$/'],
            'prenom' => ['required', 'string', 'max:120', 'regex:/^[^0-9]+$/'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', new Enum(RoleUtilisateur::class)],
            'telephone' => ['required', 'string', 'regex:/^\d{10}$/'],
            'departement' => ['required_if:role,EMPLOYE', 'nullable', 'string', Rule::in(self::DEPARTEMENTS)],
            'localisation' => ['required', 'string', Rule::in(self::SITES)],
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