<?php

/*
|--------------------------------------------------------------------------
| Configuration du Parc IT
|--------------------------------------------------------------------------
| Toutes les valeurs ajustables du domaine vivent ici (aucune valeur "en dur"
| dans les classes métier). Elles proviennent de l'environnement quand c'est
| pertinent (secrets, identifiants).
*/

return [

    // Compte administrateur initial (semé) — lu depuis l'environnement.
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@hk.local'),
        'password' => env('ADMIN_PASSWORD', 'ChangeMe!2026'),
        'nom' => env('ADMIN_NOM', 'Admin'),
        'prenom' => env('ADMIN_PRENOM', 'IT'),
    ],

    // Seuils par défaut des métriques (%), servant aussi de base aux RegleAlerte.
    'seuils' => [
        'cpu' => env('SEUIL_CPU', 85),
        'ram' => env('SEUIL_RAM', 85),
        'disque' => env('SEUIL_DISQUE', 90),
    ],

    // Simulation de supervision (en l'absence d'agents SNMP réels).
    'supervision' => [
        'intervalle_minutes' => env('SUPERVISION_INTERVALLE', 5),
        'historique_jours' => env('SUPERVISION_HISTORIQUE_JOURS', 7),
    ],

    // Scan réseau simulé.
    'scan' => [
        'plage_par_defaut' => env('SCAN_PLAGE', '192.168.1.0/24'),
        'min_equipements' => env('SCAN_MIN', 2),
        'max_equipements' => env('SCAN_MAX', 6),
    ],

    // Prédiction (IA) — horizon et seuil de probabilité déclenchant une alerte.
    'prediction' => [
        'horizon_jours' => env('PREDICTION_HORIZON', 7),
        'seuil_probabilite' => env('PREDICTION_SEUIL', 0.7),
    ],

];
