<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Alerte
 */
class AlerteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'severite' => $this->severite->value,
            'severiteLabel' => $this->severite->label(),
            'message' => $this->message,
            'etat' => $this->etat->value,
            'etatLabel' => $this->etat->label(),
            'dateCreation' => $this->date_creation,
            'dateResolution' => $this->date_resolution,
            'equipement' => $this->whenLoaded('equipement', fn () => [
                'id' => $this->equipement->id,
                'nom' => $this->equipement->nom,
            ]),
            'regle' => $this->whenLoaded('regle', fn () => $this->regle?->nom),
        ];
    }
}
