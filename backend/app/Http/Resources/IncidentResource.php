<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Incident
 */
class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'statut' => $this->statut->value,
            'statutLabel' => $this->statut->label(),
            'priorite' => $this->priorite->value,
            'prioriteLabel' => $this->priorite->label(),
            'solution' => $this->solution,
            'dateSignalement' => $this->date_signalement,
            'dateResolution' => $this->date_resolution,
            'equipement' => $this->whenLoaded('equipement', fn () => [
                'id' => $this->equipement->id,
                'nom' => $this->equipement->nom,
            ]),
            'signalePar' => $this->whenLoaded('employe', fn () => $this->employe?->nomComplet()),
            'traitePar' => $this->whenLoaded('technicien', fn () => $this->technicien?->nomComplet()),
        ];
    }
}
