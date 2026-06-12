<?php

namespace App\Services;

use App\Enums\EtatEquipement;
use App\Enums\EtatAlerte;
use App\Models\Alerte;
use App\Models\Equipement;
use App\Models\Metrique;
use App\Models\RegleAlerte;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Supervision des ressources (CPU/RAM/Disque). En l'absence d'agents SNMP
 * réels, les métriques sont simulées ; le moteur d'évaluation des règles, lui,
 * est bien réel et produit les alertes.
 */
class SupervisionService
{
    /**
     * Produit un relevé pour chaque équipement en ligne, puis évalue les
     * règles actives. Retourne le nombre de métriques et d'alertes générées.
     *
     * @return array{metriques: int, alertes: int}
     */
    public function tick(): array
    {
        $regles = RegleAlerte::where('actif', true)->get();
        $equipements = Equipement::where('etat', EtatEquipement::EN_LIGNE)->get();
        $alertesCreees = 0;

        foreach ($equipements as $equipement) {
            // Quelques machines tournent « chaud » de façon déterministe.
            $metrique = $this->releverMetrique($equipement, now(), $equipement->id % 3 === 0);
            $alertesCreees += $this->evaluer($equipement, $metrique, $regles);
        }

        return ['metriques' => $equipements->count(), 'alertes' => $alertesCreees];
    }

    /** Crée un relevé simulé pour un équipement à un instant donné. */
    public function releverMetrique(Equipement $equipement, CarbonInterface $instant, bool $chargeElevee = false): Metrique
    {
        // Une charge de base stable par équipement + une variation horaire.
        $base = $this->chargeBase($equipement->id, $chargeElevee);
        $heure = (int) $instant->format('G');
        $facteurOuvre = ($heure >= 8 && $heure <= 19) ? 1.0 : 0.8;

        return $equipement->metriques()->create([
            'date_heure' => $instant,
            'cpu_usage' => $this->borne($base['cpu'] * $facteurOuvre + $this->bruit(12)),
            'ram_usage' => $this->borne($base['ram'] * $facteurOuvre + $this->bruit(10)),
            'disk_usage' => $this->borne($base['disk'] + $this->bruit(3)), // dérive lente
        ]);
    }

    /**
     * Rejoue un historique de métriques (pour les graphiques de supervision).
     * Un point par intervalle sur la fenêtre configurée.
     */
    public function genererHistorique(Equipement $equipement): int
    {
        $jours = (int) config('parc.supervision.historique_jours');
        $pas = (int) config('parc.supervision.intervalle_minutes');
        $debut = now()->subDays($jours);
        $points = 0;

        for ($t = $debut->copy(); $t->lt(now()); $t->addMinutes($pas)) {
            $this->releverMetrique($equipement, $t->copy());
            $points++;
        }

        return $points;
    }

    /**
     * Évalue les règles contre un relevé. Crée une alerte par règle franchie,
     * en évitant les doublons tant qu'une alerte de même type reste active.
     *
     * @param  Collection<int, RegleAlerte>|null  $regles  règles actives (chargées si null)
     */
    public function evaluer(Equipement $equipement, Metrique $metrique, ?Collection $regles = null): int
    {
        $regles ??= RegleAlerte::where('actif', true)->get();
        $creees = 0;

        foreach ($regles as $regle) {
            if (! $regle->evaluer($metrique)) {
                continue;
            }

            $dejaActive = Alerte::where('equipement_id', $equipement->id)
                ->where('type', $regle->type_alerte)
                ->whereIn('etat', [EtatAlerte::ACTIVE, EtatAlerte::EN_COURS])
                ->exists();

            if ($dejaActive) {
                continue;
            }

            $valeur = round((float) $metrique->valeurCible($regle->metrique_cible), 1);
            Alerte::create([
                'equipement_id' => $equipement->id,
                'regle_alerte_id' => $regle->id,
                'type' => $regle->type_alerte,
                'severite' => $regle->severite,
                'message' => __('alertes.seuil_franchi', [
                    'regle' => $regle->nom,
                    'cible' => strtoupper($regle->metrique_cible),
                    'valeur' => $valeur,
                    'operateur' => $regle->operateur,
                    'seuil' => $regle->seuil,
                    'equipement' => $equipement->nom,
                ]),
                'date_creation' => $metrique->date_heure,
                'etat' => EtatAlerte::ACTIVE,
            ]);
            $creees++;
        }

        return $creees;
    }

    /** Profil de charge déterministe par équipement (stable d'un tick à l'autre). */
    private function chargeBase(int $id, bool $chaud): array
    {
        mt_srand($id * 7919);
        // Une machine « chaude » tourne en charge soutenue, disque presque plein
        // — de quoi déclencher des alertes réalistes.
        $base = [
            'cpu' => $chaud ? mt_rand(78, 95) : mt_rand(25, 55),
            'ram' => $chaud ? mt_rand(75, 93) : mt_rand(35, 65),
            'disk' => $chaud ? mt_rand(90, 98) : mt_rand(40, 84),
        ];
        mt_srand();

        return $base;
    }

    private function bruit(float $amplitude): float
    {
        return (mt_rand(-1000, 1000) / 1000) * $amplitude;
    }

    private function borne(float $valeur): float
    {
        return round(max(0, min(100, $valeur)), 1);
    }
}
