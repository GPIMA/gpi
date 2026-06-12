<?php

namespace App\Services;

use App\Enums\EtatAlerte;
use App\Enums\EtatEquipement;
use App\Enums\Severite;
use App\Enums\TypeAlerte;
use App\Models\Alerte;
use App\Models\Equipement;
use App\Models\ModeleIA;
use App\Models\Prediction;
use Illuminate\Support\Collection;

/**
 * Prédiction des pannes. Le « modèle » est ici une projection statistique de la
 * tendance récente des métriques (régression linéaire simple) ; l'interface
 * predire()/genererAlertePreventive() reste celle du diagramme, prête à
 * accueillir un vrai modèle entraîné.
 */
class PredictionService
{
    /** Métrique cible → type de panne associé. */
    private const PANNES = [
        'cpu' => TypeAlerte::CPU_OVERLOAD,
        'ram' => TypeAlerte::RAM_OVERLOAD,
        'disque' => TypeAlerte::DISK_FULL,
    ];

    /** Génère une prédiction par équipement en ligne. */
    public function genererPourParc(?ModeleIA $modele = null): Collection
    {
        $modele ??= ModeleIA::firstOrFail();

        return Equipement::where('etat', EtatEquipement::EN_LIGNE)
            ->get()
            ->map(fn (Equipement $e) => $this->predire($e, $modele))
            ->filter()
            ->values();
    }

    /**
     * Projette la tendance des dernières métriques pour estimer la panne la
     * plus probable et son horizon. Retourne null si l'historique est vide.
     */
    public function predire(Equipement $equipement, ModeleIA $modele): ?Prediction
    {
        $metriques = $equipement->metriques()->latest('date_heure')->limit(24)->get()->reverse()->values();
        if ($metriques->isEmpty()) {
            return null;
        }

        $horizon = (int) config('parc.prediction.horizon_jours');

        // Projection par ressource ; on retient la plus à risque.
        $projections = [
            'cpu' => $this->projeter($metriques, 'cpu_usage', $horizon),
            'ram' => $this->projeter($metriques, 'ram_usage', $horizon),
            'disque' => $this->projeter($metriques, 'disk_usage', $horizon),
        ];
        $cible = collect($projections)->sortDesc()->keys()->first();
        $projete = $projections[$cible];

        // Probabilité : 60 % projeté → 0, 100 % projeté → 1.
        $probabilite = round(max(0, min(1, ($projete - 60) / 40)), 2);

        $prediction = $equipement->predictions()->create([
            'modele_ia_id' => $modele->id,
            'date_generation' => now(),
            'type_panne' => self::PANNES[$cible],
            'probabilite' => $probabilite,
            'horizon_jours' => $horizon,
        ]);

        if ($probabilite >= (float) config('parc.prediction.seuil_probabilite')) {
            $this->genererAlertePreventive($prediction, $equipement);
        }

        return $prediction;
    }

    /** Crée une alerte préventive à partir d'une prédiction (sans doublon actif). */
    private function genererAlertePreventive(Prediction $prediction, Equipement $equipement): ?Alerte
    {
        $existe = Alerte::where('equipement_id', $equipement->id)
            ->where('type', $prediction->type_panne)
            ->whereNotNull('prediction_id')
            ->whereIn('etat', [EtatAlerte::ACTIVE, EtatAlerte::EN_COURS])
            ->exists();

        if ($existe) {
            return null;
        }

        return Alerte::create([
            'equipement_id' => $equipement->id,
            'prediction_id' => $prediction->id,
            'type' => $prediction->type_panne,
            'severite' => $this->severite($prediction->probabilite),
            'message' => __('predictions.alerte_preventive', [
                'type' => $prediction->type_panne->label(),
                'horizon' => $prediction->horizon_jours,
                'prob' => round($prediction->probabilite * 100),
                'equipement' => $equipement->nom,
            ]),
            'date_creation' => now(),
            'etat' => EtatAlerte::ACTIVE,
        ]);
    }

    /**
     * Niveau projeté à `horizon` jours : moyenne récente débruitée + tendance
     * bornée (la pente d'un historique horaire bruité est plafonnée pour éviter
     * une extrapolation absurde sur plusieurs jours).
     */
    private function projeter(Collection $metriques, string $champ, int $horizon): float
    {
        $valeurs = $metriques->pluck($champ);
        $taille = max(1, (int) floor($valeurs->count() / 3));

        $recent = $valeurs->slice(-$taille)->avg();          // moyenne des plus récents
        $ancien = $valeurs->take($taille)->avg();            // moyenne des plus anciens

        $jours = max(0.5, $metriques->first()->date_heure->diffInDays($metriques->last()->date_heure) ?: 0.5);
        $pentePj = ($recent - $ancien) / $jours;             // par jour

        // Effet de tendance plafonné à ±15 points sur l'horizon.
        $tendance = max(-15, min(15, $pentePj * $horizon));

        return max(0, min(100, $recent + $tendance));
    }

    private function severite(float $probabilite): Severite
    {
        return match (true) {
            $probabilite >= 0.85 => Severite::CRITIQUE,
            $probabilite >= 0.70 => Severite::ELEVEE,
            $probabilite >= 0.50 => Severite::MOYENNE,
            default => Severite::FAIBLE,
        };
    }
}
