<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DemandeChangementEtat
 */
class DemandeChangementEtatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipement' => $this->whenLoaded('equipement', fn () => [
                'id' => $this->equipement->id,
                'nom' => $this->equipement->nom,
                'type' => $this->equipement->type->value,
                'typeLabel' => $this->equipement->type->label(),
                'localisation' => $this->equipement->localisation,
            ]),
            'demandeur' => $this->whenLoaded('demandeur', fn () => [
                'id' => $this->demandeur->id,
                'nomComplet' => $this->demandeur->nomComplet(),
            ]),
            'etatActuel' => $this->etat_actuel->value,
            'etatActuelLabel' => $this->etat_actuel->label(),
            'etatDemande' => $this->etat_demande->value,
            'etatDemandeLabel' => $this->etat_demande->label(),
            'statut' => $this->statut->value,
            'statutLabel' => $this->statut->label(),
            'motif' => $this->motif,
            'traitePar' => $this->whenLoaded('traitePar', fn () => $this->traitePar?->nomComplet()),
            'traiteLe' => $this->traite_le,
            'commentaireTraitement' => $this->commentaire_traitement,
            'createdAt' => $this->created_at,
        ];
    }
}
