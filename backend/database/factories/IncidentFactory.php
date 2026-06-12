<?php

namespace Database\Factories;

use App\Enums\Severite;
use App\Enums\StatutIncident;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    private const PANNES = [
        ['Démarrage impossible', "Le poste ne s'allume plus depuis ce matin."],
        ['Lenteurs importantes', 'Application métier très lente, blocages fréquents.'],
        ['Écran noir intermittent', "L'écran s'éteint de façon aléatoire."],
        ['Pas de connexion réseau', "Plus d'accès au réseau ni à Internet."],
        ['Imprimante hors service', 'Bourrage papier récurrent, impressions impossibles.'],
        ['Surchauffe', "Le ventilateur tourne en continu, l'appareil chauffe."],
    ];

    public function definition(): array
    {
        [$titre, $description] = fake()->randomElement(self::PANNES);

        return [
            'titre' => $titre,
            'description' => $description,
            'statut' => StatutIncident::OUVERT,
            'priorite' => fake()->randomElement(Severite::cases()),
            'date_signalement' => fake()->dateTimeBetween('-20 days', 'now'),
        ];
    }
}
