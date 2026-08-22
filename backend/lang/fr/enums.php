<?php

return [

    'type_equipement' => [
        'PC' => 'Poste de travail',
        'SERVEUR' => 'Serveur',
        'IMPRIMANTE' => 'Imprimante',
        'ROUTEUR' => 'Routeur',
        'SWITCH' => 'Commutateur',
        'SOURIS' => 'Souris',
        'CLAVIER' => 'Clavier',
        'ECRAN' => 'Écran',
        'SOCLE' => 'Socle',
    ],

    'etat_equipement' => [
        'EN_LIGNE' => 'En ligne',
        'HORS_LIGNE' => 'Hors ligne',
        'EN_PANNE' => 'En panne',
        'MAINTENANCE' => 'En maintenance',
        'EN_MAINTENANCE' => 'En maintenance',
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
        'EN_MAINTENANCE' => 'En maintenance',
        'RESOLU' => 'Résolu',
        'FERME' => 'Fermé',
    ],

    'motif_retour_poste' => [
        'MAINTENANCE_SUR_PLACE' => 'La maintenance va s\'effectuer sur le champ',
        'NOUVELLE_DATE' => 'La maintenance va prendre plus de temps, nouvelle date de restitution',
        'POSTE_REMPLACE' => 'Le poste est endommagé, un nouveau poste est attribué',
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

    'statut_demande_changement_etat' => [
        'EN_ATTENTE' => 'En attente',
        'APPROUVEE' => 'Approuvée',
        'REJETEE' => 'Rejetée',
    ],

];
