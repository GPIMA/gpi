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
        return [
            'titre' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'equipementId' => ['required', Rule::exists('equipements', 'id')],
            'priorite' => ['required', new Enum(Severite::class)],
        ];
    }
}
