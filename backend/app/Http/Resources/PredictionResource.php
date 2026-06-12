<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Prediction
 */
class PredictionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'typePanne' => $this->type_panne->value,
            'typePanneLabel' => $this->type_panne->label(),
            'probabilite' => $this->probabilite,
            'horizonJours' => $this->horizon_jours,
            'dateGeneration' => $this->date_generation,
            'equipement' => $this->whenLoaded('equipement', fn () => [
                'id' => $this->equipement->id,
                'nom' => $this->equipement->nom,
            ]),
            'modele' => $this->whenLoaded('modele', fn () => [
                'nom' => $this->modele->nom,
                'algorithme' => $this->modele->algorithme,
                'version' => $this->modele->version,
            ]),
        ];
    }
}
