<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RegleAlerte
 */
class RegleAlerteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'metriqueCible' => $this->metrique_cible,
            'operateur' => $this->operateur,
            'seuil' => $this->seuil,
            'severite' => $this->severite->value,
            'severiteLabel' => $this->severite->label(),
            'typeAlerte' => $this->type_alerte->value,
            'typeAlerteLabel' => $this->type_alerte->label(),
            'actif' => $this->actif,
        ];
    }
}
