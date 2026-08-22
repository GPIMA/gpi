<?php

namespace Database\Seeders;

use App\Models\Equipement;
use Illuminate\Database\Seeder;

/**
 * Exemples de périphériques (souris, clavier, écran, socle) pour tester ces
 * types d'équipement. Contrairement à DatabaseSeeder, ce seeder n'est pas
 * bloqué si des équipements existent déjà : lancez-le à tout moment avec
 *
 *   php artisan db:seed --class=PeripheriquesDemoSeeder
 *
 * updateOrCreate() sur le nom rend l'opération sûre à relancer plusieurs fois.
 */
class PeripheriquesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $peripheriques = [
            ['nom' => 'SOURIS-COMPTA-01', 'type' => 'SOURIS', 'marque' => 'Logitech', 'modele' => 'MX Master 3', 'numero_serie' => 'LGT-MX3-001', 'etat' => 'EN_LIGNE', 'localisation' => 'Casablanca'],
            ['nom' => 'SOURIS-RH-01', 'type' => 'SOURIS', 'marque' => 'HP', 'modele' => '125', 'numero_serie' => 'HP-125-001', 'etat' => 'EN_LIGNE', 'localisation' => 'Rabat'],
            ['nom' => 'CLAVIER-COMPTA-01', 'type' => 'CLAVIER', 'marque' => 'Logitech', 'modele' => 'MX Keys', 'numero_serie' => 'LGT-MXK-001', 'etat' => 'EN_LIGNE', 'localisation' => 'Casablanca'],
            ['nom' => 'CLAVIER-RH-01', 'type' => 'CLAVIER', 'marque' => 'HP', 'modele' => 'K120', 'numero_serie' => 'HP-K120-001', 'etat' => 'EN_LIGNE', 'localisation' => 'Rabat'],
            ['nom' => 'ECRAN-DIR-01', 'type' => 'ECRAN', 'marque' => 'Dell', 'modele' => 'P2422H', 'numero_serie' => 'DL-P2422-001', 'etat' => 'EN_LIGNE', 'localisation' => 'Tanger'],
            ['nom' => 'ECRAN-IT-01', 'type' => 'ECRAN', 'marque' => 'HP', 'modele' => 'E24 G5', 'numero_serie' => 'HP-E24-001', 'etat' => 'HORS_LIGNE', 'localisation' => 'Casablanca'],
            ['nom' => 'SOCLE-IT-01', 'type' => 'SOCLE', 'marque' => 'Dell', 'modele' => 'WD19S', 'numero_serie' => 'DL-WD19-001', 'etat' => 'EN_LIGNE', 'localisation' => 'Rabat'],
            ['nom' => 'SOCLE-DIR-01', 'type' => 'SOCLE', 'marque' => 'HP', 'modele' => 'USB-C Dock G5', 'numero_serie' => 'HP-DCK-001', 'etat' => 'EN_MAINTENANCE', 'localisation' => 'Tanger'],
        ];

        foreach ($peripheriques as $peripherique) {
            Equipement::updateOrCreate(
                ['nom' => $peripherique['nom']],
                $peripherique + [
                    // Périphériques : pas d'adresse réseau propre.
                    'adresse_ip' => null,
                    'adresse_mac' => null,
                    'date_acquisition' => now()->subMonths(random_int(1, 24)),
                ],
            );
        }
    }
}
