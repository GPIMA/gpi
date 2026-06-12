<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nomComplet' => $this->nomComplet(),
            'email' => $this->email,
            'telephone' => $this->telephone,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'specialite' => $this->specialite,
            'departement' => $this->departement,
            'dateCreation' => $this->created_at,
        ];
    }
}
