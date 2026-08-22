<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Incident
 */
class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pieceJointes = [];
        if ($this->piece_jointe) {
            $pieceJointes[] = [
                'url' => config('app.url') . Storage::url($this->piece_jointe),
                'nom' => 'Pièce jointe',
            ];
        }
        foreach ($this->piecesJointes as $p) {
            $pieceJointes[] = [
                'url' => config('app.url') . Storage::url($p->chemin),
                'nom' => $p->nom_original ?? 'Pièce jointe',
            ];
        }

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'titre' => $this->titre,
            'description' => $this->description,
            'statut' => $this->statut->value,
            'statutLabel' => $this->statut->label(),
            'priorite' => $this->priorite->value,
            'prioriteLabel' => $this->priorite->label(),
            'solution' => $this->solution,
            'dateSignalement' => $this->date_signalement,
            'dateResolution' => $this->date_resolution,
            'dateRestitutionPrevue' => $this->date_restitution_prevue,
            // Marque que le poste a déjà été reçu par le technicien : permet
            // de distinguer "l'employé doit ramener le poste" (avant) de "le
            // poste réparé lui sera rendu" (après), qui partagent le même
            // champ dateRestitutionPrevue mais un sens opposé.
            'dateReceptionPoste' => $this->date_reception_poste,
            'pieceJointes' => $pieceJointes,
            'equipement' => $this->whenLoaded('equipement', fn () => [
                'id' => $this->equipement->id,
                'nom' => $this->equipement->nom,
                'type' => $this->equipement->type?->value,
                'localisation' => $this->equipement->localisation,
            ]),
            'employeId' => $this->employe_id,
            'signalePar' => $this->whenLoaded('employe', fn () => $this->employe?->nomComplet()),
            // Rôle de la personne concernée par l'incident — permet de
            // distinguer sur l'interface les incidents d'un employé de ceux
            // d'un membre du staff (technicien/admin/super admin) qui a
            // déclaré un problème pour lui-même.
            'signaleParRole' => $this->whenLoaded('employe', fn () => $this->employe?->role?->value),
            'signaleParRoleLabel' => $this->whenLoaded('employe', fn () => $this->employe?->role?->label()),
            'traitePar' => $this->whenLoaded('technicien', fn () => $this->technicien?->nomComplet()),
        ];
    }
}