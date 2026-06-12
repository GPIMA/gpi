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
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'marque' => $this->marque,
            'modele' => $this->modele,
            'adresseIP' => $this->adresse_ip,
            'adresseMAC' => $this->adresse_mac,
            'etat' => $this->etat->value,
            'etatLabel' => $this->etat->label(),
            'localisation' => $this->localisation,
            'dateAcquisition' => $this->date_acquisition?->toDateString(),
            'affectation' => $this->whenLoaded('affectationActive', function () {
                $active = $this->affectationActive->first();

                return $active && $active->relationLoaded('employe') && $active->employe
                    ? ['id' => $active->id, 'employe' => $active->employe->nomComplet()]
                    : null;
            }),
            'dateCreation' => $this->created_at,
        ];
    }
}
