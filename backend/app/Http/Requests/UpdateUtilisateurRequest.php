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
            'nom' => ['sometimes', 'required', 'string', 'max:120'],
            'prenom' => ['sometimes', 'required', 'string', 'max:120'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', Password::min(8)],
            'role' => ['sometimes', 'required', new Enum(RoleUtilisateur::class)],
            'telephone' => ['nullable', 'string', 'max:40'],
            'specialite' => ['nullable', 'string', 'max:120'],
            'departement' => ['nullable', 'string', 'max:120'],
        ];
    }
}
