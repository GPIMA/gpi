<?php
// app/Services/HistoriqueService.php

namespace App\Services;

use App\Models\Historique;

class HistoriqueService
{
    public static function log(
        int $userId,
        ?int $equipementId,
        string $action,
        string $description,
        array $avant = [],
        array $apres = [],
        ?int $auteurId = null,
        ?int $incidentId = null,
    ): void {
        Historique::create([
            'user_id' => $userId,
            'auteur_id' => $auteurId,
            'equipement_id' => $equipementId,
            'incident_id' => $incidentId,
            'action' => $action,
            'description' => $description,
            'donnees_avant' => $avant ?: null,
            'donnees_apres' => $apres ?: null,
        ]);
    }
}