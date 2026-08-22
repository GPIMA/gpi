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
        $affectationActive = $this->relationLoaded('affectationActive')
            ? $this->affectationActive->first()
            : null;

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nomComplet' => $this->nomComplet(),
            'email' => $this->email,
            'telephone' => $this->telephone,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'departement' => $this->departement,
            'localisation' => $this->localisation,
            'posteActuel' => $this->whenLoaded('affectationActive', function () use ($affectationActive) {
                return $affectationActive && $affectationActive->equipement
                    ? [
                        'id' => $affectationActive->equipement->id,
                        'nom' => $affectationActive->equipement->nom,
                        'type' => $affectationActive->equipement->type?->value,
                        'typeLabel' => $affectationActive->equipement->type?->label(),
                    ]
                    : null;
            }),
            'dateCreation' => $this->created_at,
        ];
    }
}