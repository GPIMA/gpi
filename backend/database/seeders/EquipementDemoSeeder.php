<?php

namespace Database\Seeders;

use App\Models\Equipement;
use Illuminate\Database\Seeder;

class EquipementDemoSeeder extends Seeder
{
    public function run(): void
    {
        $equipements = [
            // — 20 Postes de travail ————————————————————————————
            ['nom' => 'PC-COMPTA-01',  'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 7090',    'numero_serie' => 'DL7090001', 'adresse_ip' => '192.168.1.10', 'adresse_mac' => 'AA:BB:CC:01:01:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-COMPTA-02',  'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 7090',    'numero_serie' => 'DL7090002', 'adresse_ip' => '192.168.1.11', 'adresse_mac' => 'AA:BB:CC:01:01:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-RH-01',      'type' => 'PC', 'marque' => 'HP',     'modele' => 'EliteDesk 880',    'numero_serie' => 'HP880001',  'adresse_ip' => '192.168.1.12', 'adresse_mac' => 'AA:BB:CC:01:02:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'PC-RH-02',      'type' => 'PC', 'marque' => 'HP',     'modele' => 'EliteDesk 880',    'numero_serie' => 'HP880002',  'adresse_ip' => '192.168.1.13', 'adresse_mac' => 'AA:BB:CC:01:02:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'PC-DIR-01',     'type' => 'PC', 'marque' => 'Lenovo', 'modele' => 'ThinkCentre M90',  'numero_serie' => 'LNV90001',  'adresse_ip' => '192.168.1.14', 'adresse_mac' => 'AA:BB:CC:01:03:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],
            ['nom' => 'PC-DIR-02',     'type' => 'PC', 'marque' => 'Lenovo', 'modele' => 'ThinkCentre M90',  'numero_serie' => 'LNV90002',  'adresse_ip' => '192.168.1.15', 'adresse_mac' => 'AA:BB:CC:01:03:02', 'etat' => 'HORS_LIGNE',    'localisation' => 'Tanger'],
            ['nom' => 'PC-IT-01',      'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 5090',    'numero_serie' => 'DL5090001', 'adresse_ip' => '192.168.1.16', 'adresse_mac' => 'AA:BB:CC:01:04:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-IT-02',      'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 5090',    'numero_serie' => 'DL5090002', 'adresse_ip' => '192.168.1.17', 'adresse_mac' => 'AA:BB:CC:01:04:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-PROD-01',    'type' => 'PC', 'marque' => 'HP',     'modele' => 'ProDesk 600',      'numero_serie' => 'HP600001',  'adresse_ip' => '192.168.1.18', 'adresse_mac' => 'AA:BB:CC:01:05:01', 'etat' => 'EN_PANNE',      'localisation' => 'Rabat'],
            ['nom' => 'PC-PROD-02',    'type' => 'PC', 'marque' => 'HP',     'modele' => 'ProDesk 600',      'numero_serie' => 'HP600002',  'adresse_ip' => '192.168.1.19', 'adresse_mac' => 'AA:BB:CC:01:05:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'PC-VENTE-01',   'type' => 'PC', 'marque' => 'Lenovo', 'modele' => 'ThinkCentre M70',  'numero_serie' => 'LNV70001',  'adresse_ip' => '192.168.1.20', 'adresse_mac' => 'AA:BB:CC:01:06:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],
            ['nom' => 'PC-VENTE-02',   'type' => 'PC', 'marque' => 'Lenovo', 'modele' => 'ThinkCentre M70',  'numero_serie' => 'LNV70002',  'adresse_ip' => '192.168.1.21', 'adresse_mac' => 'AA:BB:CC:01:06:02', 'etat' => 'EN_MAINTENANCE', 'localisation' => 'Tanger'],
            ['nom' => 'PC-MKTG-01',    'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 3090',    'numero_serie' => 'DL3090001', 'adresse_ip' => '192.168.1.22', 'adresse_mac' => 'AA:BB:CC:01:07:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-MKTG-02',    'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 3090',    'numero_serie' => 'DL3090002', 'adresse_ip' => '192.168.1.23', 'adresse_mac' => 'AA:BB:CC:01:07:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-JUR-01',     'type' => 'PC', 'marque' => 'HP',     'modele' => 'EliteDesk 800',    'numero_serie' => 'HP800001',  'adresse_ip' => '192.168.1.24', 'adresse_mac' => 'AA:BB:CC:01:08:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'PC-JUR-02',     'type' => 'PC', 'marque' => 'HP',     'modele' => 'EliteDesk 800',    'numero_serie' => 'HP800002',  'adresse_ip' => '192.168.1.25', 'adresse_mac' => 'AA:BB:CC:01:08:02', 'etat' => 'HORS_LIGNE',    'localisation' => 'Rabat'],
            ['nom' => 'PC-LOG-01',     'type' => 'PC', 'marque' => 'Lenovo', 'modele' => 'ThinkCentre M80',  'numero_serie' => 'LNV80001',  'adresse_ip' => '192.168.1.26', 'adresse_mac' => 'AA:BB:CC:01:09:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],
            ['nom' => 'PC-LOG-02',     'type' => 'PC', 'marque' => 'Lenovo', 'modele' => 'ThinkCentre M80',  'numero_serie' => 'LNV80002',  'adresse_ip' => '192.168.1.27', 'adresse_mac' => 'AA:BB:CC:01:09:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],
            ['nom' => 'PC-ACHAT-01',   'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 7080',    'numero_serie' => 'DL7080001', 'adresse_ip' => '192.168.1.28', 'adresse_mac' => 'AA:BB:CC:01:10:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'PC-ACHAT-02',   'type' => 'PC', 'marque' => 'Dell',   'modele' => 'OptiPlex 7080',    'numero_serie' => 'DL7080002', 'adresse_ip' => '192.168.1.29', 'adresse_mac' => 'AA:BB:CC:01:10:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],

            // — 5 Serveurs ————————————————————————————————————
            ['nom' => 'SRV-WEB-01',    'type' => 'SERVEUR', 'marque' => 'HPE',  'modele' => 'ProLiant DL380',  'numero_serie' => 'HPE380001', 'adresse_ip' => '192.168.10.1', 'adresse_mac' => 'BB:CC:DD:02:01:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'SRV-DB-01',     'type' => 'SERVEUR', 'marque' => 'Dell', 'modele' => 'PowerEdge R750',  'numero_serie' => 'DLR750001', 'adresse_ip' => '192.168.10.2', 'adresse_mac' => 'BB:CC:DD:02:01:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'SRV-MAIL-01',   'type' => 'SERVEUR', 'marque' => 'HPE',  'modele' => 'ProLiant DL360',  'numero_serie' => 'HPE360001', 'adresse_ip' => '192.168.10.3', 'adresse_mac' => 'BB:CC:DD:02:01:03', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'SRV-FILE-01',   'type' => 'SERVEUR', 'marque' => 'Dell', 'modele' => 'PowerEdge R650',  'numero_serie' => 'DLR650001', 'adresse_ip' => '192.168.10.4', 'adresse_mac' => 'BB:CC:DD:02:01:04', 'etat' => 'EN_MAINTENANCE', 'localisation' => 'Rabat'],
            ['nom' => 'SRV-BACKUP-01', 'type' => 'SERVEUR', 'marque' => 'HPE',  'modele' => 'ProLiant DL160',  'numero_serie' => 'HPE160001', 'adresse_ip' => '192.168.10.5', 'adresse_mac' => 'BB:CC:DD:02:01:05', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],

            // — 6 Imprimantes ————————————————————————————————
            ['nom' => 'IMP-RH-01',     'type' => 'IMPRIMANTE', 'marque' => 'HP',      'modele' => 'LaserJet M428',   'numero_serie' => 'HPM428001', 'adresse_ip' => '192.168.20.1', 'adresse_mac' => 'CC:DD:EE:03:01:01', 'etat' => 'EN_LIGNE',   'localisation' => 'Rabat'],
            ['nom' => 'IMP-COMPTA-01', 'type' => 'IMPRIMANTE', 'marque' => 'Canon',   'modele' => 'i-SENSYS LBP',    'numero_serie' => 'CNL001',    'adresse_ip' => '192.168.20.2', 'adresse_mac' => 'CC:DD:EE:03:01:02', 'etat' => 'EN_LIGNE',   'localisation' => 'Casablanca'],
            ['nom' => 'IMP-DIR-01',    'type' => 'IMPRIMANTE', 'marque' => 'HP',      'modele' => 'LaserJet M507',   'numero_serie' => 'HPM507001', 'adresse_ip' => '192.168.20.3', 'adresse_mac' => 'CC:DD:EE:03:01:03', 'etat' => 'EN_LIGNE',   'localisation' => 'Casablanca'],
            ['nom' => 'IMP-VENTE-01',  'type' => 'IMPRIMANTE', 'marque' => 'Canon',   'modele' => 'i-SENSYS MF',     'numero_serie' => 'CNMF001',   'adresse_ip' => '192.168.20.4', 'adresse_mac' => 'CC:DD:EE:03:01:04', 'etat' => 'HORS_LIGNE', 'localisation' => 'Tanger'],
            ['nom' => 'IMP-IT-01',     'type' => 'IMPRIMANTE', 'marque' => 'Brother', 'modele' => 'HL-L6400DW',      'numero_serie' => 'BRL640001', 'adresse_ip' => '192.168.20.5', 'adresse_mac' => 'CC:DD:EE:03:01:05', 'etat' => 'EN_LIGNE',   'localisation' => 'Rabat'],
            ['nom' => 'IMP-LOG-01',    'type' => 'IMPRIMANTE', 'marque' => 'Brother', 'modele' => 'HL-L5200DW',      'numero_serie' => 'BRL520001', 'adresse_ip' => '192.168.20.6', 'adresse_mac' => 'CC:DD:EE:03:01:06', 'etat' => 'EN_PANNE',   'localisation' => 'Tanger'],

            // — 10 Routeurs ——————————————————————————————————
            ['nom' => 'RTR-CORE-01',   'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4431', 'numero_serie' => 'CSC4431001', 'adresse_ip' => '10.0.0.1', 'adresse_mac' => 'DD:EE:FF:04:01:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'RTR-CORE-02',   'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4431', 'numero_serie' => 'CSC4431002', 'adresse_ip' => '10.0.0.2', 'adresse_mac' => 'DD:EE:FF:04:01:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'RTR-AGC-RBT',   'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4321', 'numero_serie' => 'CSC4321001', 'adresse_ip' => '10.0.1.1', 'adresse_mac' => 'DD:EE:FF:04:02:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'RTR-AGC-TNG',   'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4321', 'numero_serie' => 'CSC4321002', 'adresse_ip' => '10.0.2.1', 'adresse_mac' => 'DD:EE:FF:04:02:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],
            ['nom' => 'RTR-DMZ-01',    'type' => 'ROUTEUR', 'marque' => 'Juniper', 'modele' => 'SRX300',   'numero_serie' => 'JNP300001',  'adresse_ip' => '10.0.3.1', 'adresse_mac' => 'DD:EE:FF:04:03:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'RTR-WAN-01',    'type' => 'ROUTEUR', 'marque' => 'Juniper', 'modele' => 'SRX345',   'numero_serie' => 'JNP345001',  'adresse_ip' => '10.0.4.1', 'adresse_mac' => 'DD:EE:FF:04:04:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'RTR-WAN-02',    'type' => 'ROUTEUR', 'marque' => 'Juniper', 'modele' => 'SRX345',   'numero_serie' => 'JNP345002',  'adresse_ip' => '10.0.4.2', 'adresse_mac' => 'DD:EE:FF:04:04:02', 'etat' => 'HORS_LIGNE',    'localisation' => 'Rabat'],
            ['nom' => 'RTR-VPN-01',    'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4221', 'numero_serie' => 'CSC4221001', 'adresse_ip' => '10.0.5.1', 'adresse_mac' => 'DD:EE:FF:04:05:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'RTR-BACKUP-01', 'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4221', 'numero_serie' => 'CSC4221002', 'adresse_ip' => '10.0.5.2', 'adresse_mac' => 'DD:EE:FF:04:05:02', 'etat' => 'EN_MAINTENANCE', 'localisation' => 'Tanger'],
            ['nom' => 'RTR-AGC-CAS',   'type' => 'ROUTEUR', 'marque' => 'Cisco',   'modele' => 'ISR 4331', 'numero_serie' => 'CSC4331001', 'adresse_ip' => '10.0.6.1', 'adresse_mac' => 'DD:EE:FF:04:06:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],

            // — 10 Commutateurs ——————————————————————————————
            ['nom' => 'SW-CORE-01',    'type' => 'SWITCH', 'marque' => 'Cisco', 'modele' => 'Catalyst 9300', 'numero_serie' => 'CSC9300001', 'adresse_ip' => '192.168.100.1',  'adresse_mac' => 'EE:FF:AA:05:01:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'SW-CORE-02',    'type' => 'SWITCH', 'marque' => 'Cisco', 'modele' => 'Catalyst 9300', 'numero_serie' => 'CSC9300002', 'adresse_ip' => '192.168.100.2',  'adresse_mac' => 'EE:FF:AA:05:01:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'SW-ACC-RBT-01', 'type' => 'SWITCH', 'marque' => 'Cisco', 'modele' => 'Catalyst 2960', 'numero_serie' => 'CSC2960001', 'adresse_ip' => '192.168.100.3',  'adresse_mac' => 'EE:FF:AA:05:02:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'SW-ACC-RBT-02', 'type' => 'SWITCH', 'marque' => 'Cisco', 'modele' => 'Catalyst 2960', 'numero_serie' => 'CSC2960002', 'adresse_ip' => '192.168.100.4',  'adresse_mac' => 'EE:FF:AA:05:02:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Rabat'],
            ['nom' => 'SW-ACC-TNG-01', 'type' => 'SWITCH', 'marque' => 'HP',    'modele' => 'Aruba 2530',    'numero_serie' => 'HPA2530001', 'adresse_ip' => '192.168.100.5',  'adresse_mac' => 'EE:FF:AA:05:03:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Tanger'],
            ['nom' => 'SW-ACC-TNG-02', 'type' => 'SWITCH', 'marque' => 'HP',    'modele' => 'Aruba 2530',    'numero_serie' => 'HPA2530002', 'adresse_ip' => '192.168.100.6',  'adresse_mac' => 'EE:FF:AA:05:03:02', 'etat' => 'HORS_LIGNE',    'localisation' => 'Tanger'],
            ['nom' => 'SW-DIST-01',    'type' => 'SWITCH', 'marque' => 'Cisco', 'modele' => 'Catalyst 3850', 'numero_serie' => 'CSC3850001', 'adresse_ip' => '192.168.100.7',  'adresse_mac' => 'EE:FF:AA:05:04:01', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'SW-DIST-02',    'type' => 'SWITCH', 'marque' => 'Cisco', 'modele' => 'Catalyst 3850', 'numero_serie' => 'CSC3850002', 'adresse_ip' => '192.168.100.8',  'adresse_mac' => 'EE:FF:AA:05:04:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
            ['nom' => 'SW-DMZ-01',     'type' => 'SWITCH', 'marque' => 'HP',    'modele' => 'Aruba 2540',    'numero_serie' => 'HPA2540001', 'adresse_ip' => '192.168.100.9',  'adresse_mac' => 'EE:FF:AA:05:05:01', 'etat' => 'EN_MAINTENANCE', 'localisation' => 'Rabat'],
            ['nom' => 'SW-ACC-CAS-01', 'type' => 'SWITCH', 'marque' => 'HP',    'modele' => 'Aruba 2540',    'numero_serie' => 'HPA2540002', 'adresse_ip' => '192.168.100.10', 'adresse_mac' => 'EE:FF:AA:05:05:02', 'etat' => 'EN_LIGNE',      'localisation' => 'Casablanca'],
        ];

        foreach ($equipements as $eq) {
            Equipement::create($eq);
        }
    }
}