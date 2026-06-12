<?php

namespace App\Services;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use App\Models\Equipement;
use App\Models\ScanReseau;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Simulates an SNMP network discovery. In production the discovery agent would
 * replace lancer(); the rest of the system (controllers, persistence, UI) is
 * unaware of how the devices were found, so the seam is clean.
 */
class ScanReseauService
{
    /** Plausible hardware per equipment type, used only to fabricate findings. */
    private const CATALOGUE = [
        'PC' => [['Dell', 'OptiPlex 7090'], ['HP', 'EliteDesk 800'], ['Lenovo', 'ThinkCentre M70']],
        'SERVEUR' => [['Dell', 'PowerEdge R750'], ['HPE', 'ProLiant DL380'], ['Supermicro', 'SYS-2029']],
        'IMPRIMANTE' => [['HP', 'LaserJet M428'], ['Canon', 'i-SENSYS'], ['Epson', 'WF-C5790']],
        'ROUTEUR' => [['Cisco', 'ISR 4331'], ['MikroTik', 'CCR2004'], ['Juniper', 'SRX340']],
        'SWITCH' => [['Cisco', 'Catalyst 9200'], ['Aruba', '2930F'], ['Netgear', 'GS748T']],
    ];

    /**
     * Run a scan over an IP range and persist the freshly discovered devices.
     *
     * @return array{scan: ScanReseau, equipements: Collection<int, Equipement>}
     */
    public function lancer(string $plageIp, ?User $operateur = null): array
    {
        $min = (int) config('parc.scan.min_equipements');
        $max = (int) config('parc.scan.max_equipements');
        $nombre = random_int($min, $max);
        $prefixe = $this->prefixeDepuisPlage($plageIp);

        return DB::transaction(function () use ($plageIp, $operateur, $nombre, $prefixe) {
            $scan = ScanReseau::create([
                'plage_ip' => $plageIp,
                'date_scan' => now(),
                'duree' => random_int(3, 25),
                'nb_detectes' => $nombre,
                'lance_par' => $operateur?->id,
            ]);

            $octets = collect(range(1, 254))->shuffle()->take($nombre);

            $equipements = $octets->map(function (int $octet) use ($scan, $prefixe) {
                $type = collect(TypeEquipement::cases())->random();
                [$marque, $modele] = collect(self::CATALOGUE[$type->value])->random();

                return Equipement::create([
                    'nom' => "{$type->value}-{$prefixe}{$octet}",
                    'type' => $type,
                    'marque' => $marque,
                    'modele' => $modele,
                    'adresse_ip' => $prefixe.$octet,
                    'adresse_mac' => $this->macAleatoire(),
                    'etat' => EtatEquipement::EN_LIGNE,
                    'localisation' => null,
                    'date_acquisition' => now()->subDays(random_int(30, 900)),
                    'scan_reseau_id' => $scan->id,
                ]);
            });

            return ['scan' => $scan, 'equipements' => $equipements->values()];
        });
    }

    private function prefixeDepuisPlage(string $plage): string
    {
        $base = explode('/', $plage)[0];                  // 192.168.1.0
        $parts = explode('.', $base);
        $parts[3] = '';                                    // strip host octet

        return implode('.', array_slice($parts, 0, 3)).'.'; // 192.168.1.
    }

    private function macAleatoire(): string
    {
        return implode(':', array_map(
            fn () => str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT),
            range(1, 6),
        ));
    }
}
