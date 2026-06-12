<?php

namespace Database\Seeders;

use App\Enums\EtatEquipement;
use App\Enums\RoleUtilisateur;
use App\Enums\Severite;
use App\Enums\TypeAlerte;
use App\Models\Affectation;
use App\Models\Equipement;
use App\Models\RegleAlerte;
use App\Models\User;
use App\Services\SupervisionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Administrator — credentials come from the environment, never the code.
        User::updateOrCreate(
            ['email' => config('parc.admin.email')],
            [
                'nom' => config('parc.admin.nom'),
                'prenom' => config('parc.admin.prenom'),
                'password' => Hash::make(config('parc.admin.password')),
                'role' => RoleUtilisateur::ADMIN,
            ],
        );

        // Demo data is seeded once. On redeploys the admin above is kept in sync
        // and we stop here, so running this seeder on every boot stays idempotent.
        if (Equipement::query()->exists()) {
            return;
        }

        // A small demo team so the interface has data to work with.
        User::factory()->technicien()->count(3)->create();
        $employes = User::factory()->employe()->count(8)->create();

        // A demo fleet.
        $equipements = Equipement::factory()->count(24)->create();

        // Assign workstations to employees (active affectations).
        $equipements
            ->where('type', \App\Enums\TypeEquipement::PC)
            ->take($employes->count())
            ->values()
            ->each(function (Equipement $pc, int $i) use ($employes) {
                $employe = $employes[$i % $employes->count()];
                Affectation::create([
                    'employe_id' => $employe->id,
                    'equipement_id' => $pc->id,
                    'date_affectation' => now()->subDays(random_int(10, 400)),
                    'statut' => 'EN_COURS',
                ]);
            });

        $this->seedReglesAlerte();
        $this->seedSupervision();
        $this->seedIncidents($employes);
        $this->seedPredictions();
    }

    /** Modèle IA + première campagne de prédictions. */
    private function seedPredictions(): void
    {
        \App\Models\ModeleIA::create([
            'nom' => 'Prédicteur de pannes',
            'algorithme' => 'Régression linéaire (tendance)',
            'date_entrainement' => now()->subDays(7),
            'precision' => 0.82,
            'version' => '1.0',
        ]);

        app(\App\Services\PredictionService::class)->genererPourParc();
    }

    /** Quelques incidents : ouverts, en cours et résolus. */
    private function seedIncidents($employes): void
    {
        $techniciens = User::where('role', RoleUtilisateur::TECHNICIEN)->get();
        $equipements = Equipement::inRandomOrder()->limit(12)->get();

        foreach ($equipements as $i => $equipement) {
            $employe = $employes->random();
            $incident = \App\Models\Incident::factory()->make([
                'equipement_id' => $equipement->id,
                'employe_id' => $employe->id,
            ]);

            // ~1/3 en cours, 1/3 résolus, 1/3 ouverts.
            if ($i % 3 === 1) {
                $incident->statut = \App\Enums\StatutIncident::EN_COURS;
                $incident->technicien_id = $techniciens->random()->id;
            } elseif ($i % 3 === 2) {
                $incident->statut = \App\Enums\StatutIncident::RESOLU;
                $incident->technicien_id = $techniciens->random()->id;
                $incident->solution = 'Intervention réalisée, équipement remis en service.';
                $incident->date_resolution = now()->subDays(random_int(0, 5));
            }

            $incident->save();
        }
    }

    /** Règles de seuil par défaut, dérivées de la configuration. */
    private function seedReglesAlerte(): void
    {
        $seuils = config('parc.seuils');

        $regles = [
            ['nom' => 'CPU élevé', 'metrique_cible' => 'cpu', 'seuil' => $seuils['cpu'], 'severite' => Severite::ELEVEE, 'type_alerte' => TypeAlerte::CPU_OVERLOAD],
            ['nom' => 'Mémoire saturée', 'metrique_cible' => 'ram', 'seuil' => $seuils['ram'], 'severite' => Severite::ELEVEE, 'type_alerte' => TypeAlerte::RAM_OVERLOAD],
            ['nom' => 'Disque presque plein', 'metrique_cible' => 'disque', 'seuil' => $seuils['disque'], 'severite' => Severite::CRITIQUE, 'type_alerte' => TypeAlerte::DISK_FULL],
        ];

        foreach ($regles as $regle) {
            RegleAlerte::create([...$regle, 'operateur' => '>=', 'actif' => true]);
        }
    }

    /** Historique de métriques (48 points horaires) + évaluation des alertes. */
    private function seedSupervision(): void
    {
        $supervision = app(SupervisionService::class);
        $enLigne = Equipement::where('etat', EtatEquipement::EN_LIGNE)->get();

        // Garantir un parc supervisé suffisant pour la démo.
        if ($enLigne->count() < 8) {
            $appoint = Equipement::where('etat', '!=', EtatEquipement::EN_LIGNE->value)
                ->limit(8 - $enLigne->count())
                ->get();
            $appoint->each->update(['etat' => EtatEquipement::EN_LIGNE]);
            $enLigne = $enLigne->concat($appoint);
        }

        // Trois machines « chaudes » garanties → quelques alertes à la clé.
        $chaudes = $enLigne->take(3)->pluck('id')->all();

        foreach ($enLigne as $equipement) {
            $chaud = in_array($equipement->id, $chaudes, true);

            // Historique horaire sur 48 h.
            for ($t = now()->subHours(48); $t->lt(now()); $t->addHour()) {
                $supervision->releverMetrique($equipement, $t->copy(), $chaud);
            }

            // Relevé courant + évaluation des règles.
            $metrique = $supervision->releverMetrique($equipement, now(), $chaud);
            $supervision->evaluer($equipement, $metrique);
        }
    }
}
