<?php

/*
|--------------------------------------------------------------------------
| Configuration du Parc IT
|--------------------------------------------------------------------------
| Toutes les valeurs ajustables du domaine vivent ici. Les identifiants,
| secrets et mots de passe restent dans l'environnement.
*/

return [

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@gpi.local'),
        'password' => env('ADMIN_PASSWORD', 'password'),
        'nom' => env('ADMIN_NOM', 'Admin'),
        'prenom' => env('ADMIN_PRENOM', 'GPI'),
    ],

    'demo_users' => [
        'employe' => [
            'email' => env('DEMO_EMPLOYE_EMAIL', 'employe@gpi.local'),
            'password' => env('DEMO_EMPLOYE_PASSWORD', 'password'),
            'nom' => env('DEMO_EMPLOYE_NOM', 'Employe'),
            'prenom' => env('DEMO_EMPLOYE_PRENOM', 'Demo'),
            'departement' => env('DEMO_EMPLOYE_DEPARTEMENT', 'Support interne'),
        ],
        'technicien' => [
            'email' => env('DEMO_TECHNICIEN_EMAIL', 'technicien@gpi.local'),
            'password' => env('DEMO_TECHNICIEN_PASSWORD', 'password'),
            'nom' => env('DEMO_TECHNICIEN_NOM', 'Technicien'),
            'prenom' => env('DEMO_TECHNICIEN_PRENOM', 'Demo'),
        ],
    ],

    'seuils' => [
        'cpu' => env('SEUIL_CPU', 85),
        'ram' => env('SEUIL_RAM', 85),
        'disque' => env('SEUIL_DISQUE', 90),
    ],

    'supervision' => [
        'intervalle_minutes' => env('SUPERVISION_INTERVALLE', 5),
        'historique_jours' => env('SUPERVISION_HISTORIQUE_JOURS', 7),
    ],

    'prediction' => [
        'horizon_jours' => env('PREDICTION_HORIZON', 7),
        'seuil_probabilite' => env('PREDICTION_SEUIL', 0.7),
    ],

];
