<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DemandeChangementEtatCommentaire
 */
class DemandeChangementEtatCommentaireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contenu' => $this->contenu,
            'auteur' => $this->auteur?->nomComplet(),
            'auteurId' => $this->auteur_id,
            'createdAt' => $this->created_at,
        ];
    }
}
