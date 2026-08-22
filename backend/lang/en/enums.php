<?php

return [

    'type_equipement' => [
        'PC' => 'Workstation',
        'SERVEUR' => 'Server',
        'IMPRIMANTE' => 'Printer',
        'ROUTEUR' => 'Router',
        'SWITCH' => 'Switch',
        'SOURIS' => 'Mouse',
        'CLAVIER' => 'Keyboard',
        'ECRAN' => 'Monitor',
        'SOCLE' => 'Docking station',
    ],

    'etat_equipement' => [
        'EN_LIGNE' => 'Online',
        'HORS_LIGNE' => 'Offline',
        'EN_PANNE' => 'Down',
        'MAINTENANCE' => 'Maintenance',
        'EN_MAINTENANCE' => 'Maintenance',
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
        'EN_MAINTENANCE' => 'Under maintenance',
        'RESOLU' => 'Resolved',
        'FERME' => 'Closed',
    ],

    'motif_retour_poste' => [
        'MAINTENANCE_SUR_PLACE' => 'Maintenance will be done on site',
        'NOUVELLE_DATE' => 'Maintenance will take longer, new return date',
        'POSTE_REMPLACE' => 'Device is damaged, a new one is assigned',
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

    'statut_demande_changement_etat' => [
        'EN_ATTENTE' => 'Pending',
        'APPROUVEE' => 'Approved',
        'REJETEE' => 'Rejected',
    ],

];
