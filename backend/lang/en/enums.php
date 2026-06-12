<?php

return [

    'type_equipement' => [
        'PC' => 'Workstation',
        'SERVEUR' => 'Server',
        'IMPRIMANTE' => 'Printer',
        'ROUTEUR' => 'Router',
        'SWITCH' => 'Switch',
    ],

    'etat_equipement' => [
        'EN_LIGNE' => 'Online',
        'HORS_LIGNE' => 'Offline',
        'EN_PANNE' => 'Down',
        'MAINTENANCE' => 'Maintenance',
    ],

    'type_alerte' => [
        'CPU_OVERLOAD' => 'CPU overload',
        'RAM_OVERLOAD' => 'Memory overload',
        'DISK_FULL' => 'Disk full',
        'DECONNEXION' => 'Disconnection',
        'PANNE' => 'Failure',
    ],

    'severite' => [
        'FAIBLE' => 'Low',
        'MOYENNE' => 'Medium',
        'ELEVEE' => 'High',
        'CRITIQUE' => 'Critical',
    ],

    'etat_alerte' => [
        'ACTIVE' => 'Active',
        'EN_COURS' => 'In progress',
        'RESOLUE' => 'Resolved',
    ],

    'canal_notification' => [
        'EMAIL' => 'Email',
        'INTERFACE' => 'Interface',
        'SMS' => 'SMS',
    ],

    'statut_incident' => [
        'OUVERT' => 'Open',
        'EN_COURS' => 'In progress',
        'RESOLU' => 'Resolved',
        'FERME' => 'Closed',
    ],

    'expediteur_type' => [
        'UTILISATEUR' => 'User',
        'CHATBOT' => 'Assistant',
    ],

    'role_utilisateur' => [
        'ADMIN' => 'Administrator',
        'TECHNICIEN' => 'Technician',
        'EMPLOYE' => 'Employee',
    ],

];
