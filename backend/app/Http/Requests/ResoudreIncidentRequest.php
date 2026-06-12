<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResoudreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solution' => ['required', 'string', 'max:2000'],
        ];
    }
}
