<?php

return [

    'type_equipement' => [
        'PC' => 'Poste de travail',
        'SERVEUR' => 'Serveur',
        'IMPRIMANTE' => 'Imprimante',
        'ROUTEUR' => 'Routeur',
        'SWITCH' => 'Commutateur',
    ],

    'etat_equipement' => [
        'EN_LIGNE' => 'En ligne',
        'HORS_LIGNE' => 'Hors ligne',
        'EN_PANNE' => 'En panne',
        'MAINTENANCE' => 'En maintenance',
    ],

    'type_alerte' => [
        'CPU_OVERLOAD' => 'Surcharge CPU',
        'RAM_OVERLOAD' => 'Surcharge mémoire',
        'DISK_FULL' => 'Disque saturé',
        'DECONNEXION' => 'Déconnexion',
        'PANNE' => 'Panne',
    ],

    'severite' => [
        'FAIBLE' => 'Faible',
        'MOYENNE' => 'Moyenne',
        'ELEVEE' => 'Élevée',
        'CRITIQUE' => 'Critique',
    ],

    'etat_alerte' => [
        'ACTIVE' => 'Active',
        'EN_COURS' => 'En cours',
        'RESOLUE' => 'Résolue',
    ],

    'canal_notification' => [
        'EMAIL' => 'E-mail',
        'INTERFACE' => 'Interface',
        'SMS' => 'SMS',
    ],

    'statut_incident' => [
        'OUVERT' => 'Ouvert',
        'EN_COURS' => 'En cours',
        'RESOLU' => 'Résolu',
        'FERME' => 'Fermé',
    ],

    'expediteur_type' => [
        'UTILISATEUR' => 'Utilisateur',
        'CHATBOT' => 'Assistant',
    ],

    'role_utilisateur' => [
        'ADMIN' => 'Administrateur',
        'TECHNICIEN' => 'Technicien',
        'EMPLOYE' => 'Employé',
    ],

];
