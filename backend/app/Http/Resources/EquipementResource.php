<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Equipement
 */
class EquipementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
       $affectationActive = $this->relationLoaded('affectationActive')
    ? $this->affectationActive->first()
    : null;
       $demandeEnAttente = $this->relationLoaded('demandeChangementEtatEnAttente')
    ? $this->demandeChangementEtatEnAttente->first()
    : null;
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'marque' => $this->marque,
            'modele' => $this->modele,
            'numeroSerie' => $this->numero_serie,
            'adresseIP' => $this->adresse_ip,
            'adresseMAC' => $this->adresse_mac,
            'etat' => $this->etat->value,
            'etatLabel' => $this->etat->label(),
            'localisation' => $this->localisation,
            'dateAcquisition' => $this->date_acquisition?->toDateString(),
            'affectation' => $this->whenLoaded('affectationActive', function () use ($affectationActive) {
    return $affectationActive && $affectationActive->employe
        ? [
            'id' => $affectationActive->id,
            'employeId' => $affectationActive->employe_id,
            'employe' => $affectationActive->employe->nomComplet(),
        ]
        : null;
}),
            'demandeChangementEtatEnAttente' => $demandeEnAttente ? [
                'id' => $demandeEnAttente->id,
                'etatActuel' => $demandeEnAttente->etat_actuel->value,
                'etatActuelLabel' => $demandeEnAttente->etat_actuel->label(),
                'etatDemande' => $demandeEnAttente->etat_demande->value,
                'etatDemandeLabel' => $demandeEnAttente->etat_demande->label(),
                'createdAt' => $demandeEnAttente->created_at,
            ] : null,
            'dateCreation' => $this->created_at,
        ];
    }
}