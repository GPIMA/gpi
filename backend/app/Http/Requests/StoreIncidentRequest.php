<?php

namespace App\Http\Requests;

use App\Enums\Severite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Un Admin/Super Admin/Technicien qui déclare un incident le fait
        // pour le compte d'un autre utilisateur : le champ "Employé concerné"
        // devient alors obligatoire. Un Employé déclare toujours pour lui-même.
        $reporterProxy = $this->user() && $this->user()->role !== \App\Enums\RoleUtilisateur::EMPLOYE;

        return [
            'titre' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'equipementId' => ['required', Rule::exists('equipements', 'id')],
            'priorite' => ['required', new Enum(Severite::class)],
            'utilisateurId' => [$reporterProxy ? 'required' : 'nullable', 'integer', Rule::exists('users', 'id')],
            'pieceJointes' => ['nullable', 'array', 'max:5'],
            'pieceJointes.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx'],
        ];
    }

    public function messages(): array
    {
        return [
            'pieceJointes.max' => 'Vous pouvez joindre 5 fichiers maximum.',
            'pieceJointes.*.max' => 'Chaque fichier ne doit pas dépasser 5 Mo.',
            'pieceJointes.*.mimes' => 'Formats acceptés : JPG, PNG, PDF, DOC, DOCX, XLSX.',
        ];
    }
}