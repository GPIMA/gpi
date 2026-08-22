<?php

namespace Database\Factories;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipement>
 */
class EquipementFactory extends Factory
{
    protected $model = Equipement::class;

    private const MODELES = [
        'PC' => [['Dell', 'OptiPlex 7090'], ['HP', 'EliteDesk 800'], ['Lenovo', 'ThinkCentre M70']],
        'SERVEUR' => [['Dell', 'PowerEdge R750'], ['HPE', 'ProLiant DL380']],
        'IMPRIMANTE' => [['HP', 'LaserJet M428'], ['Canon', 'i-SENSYS']],
        'ROUTEUR' => [['Cisco', 'ISR 4331'], ['MikroTik', 'CCR2004']],
        'SWITCH' => [['Cisco', 'Catalyst 9200'], ['Aruba', '2930F']],
        'SOURIS' => [['Logitech', 'MX Master 3'], ['HP', '125']],
        'CLAVIER' => [['Logitech', 'MX Keys'], ['HP', 'K120']],
        'ECRAN' => [['Dell', 'P2422H'], ['HP', 'E24 G5']],
        'SOCLE' => [['Dell', 'WD19S'], ['HP', 'USB-C Dock G5']],
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(TypeEquipement::cases());
        [$marque, $modele] = fake()->randomElement(self::MODELES[$type->value]);

        return [
            'nom' => $type->value.'-'.fake()->unique()->numberBetween(100, 999),
            'type' => $type,
            'marque' => $marque,
            'modele' => $modele,
            'adresse_ip' => fake()->localIpv4(),
            'adresse_mac' => fake()->macAddress(),
            'etat' => fake()->randomElement(EtatEquipement::cases()),
            'localisation' => fake()->randomElement([
                'Siège · RDC', 'Siège · 1er étage', 'Siège · 2e étage',
                'Datacenter', 'Agence Nord', 'Agence Sud',
            ]),
            'date_acquisition' => fake()->dateTimeBetween('-3 years', '-1 month'),
        ];
    }
}
