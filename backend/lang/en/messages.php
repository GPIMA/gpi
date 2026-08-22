<?php

return [
    'forbidden' => 'You are not authorized to perform this action.',
    'identifiants_invalides' => 'Invalid credentials.',
    'deconnecte' => 'Signed out successfully.',
    'introuvable' => 'Resource not found.',

    'equipement' => [
        'cree' => 'Equipment added.',
        'modifie' => 'Equipment updated.',
        'supprime' => 'Equipment removed.',
        'changement_etat_en_attente' => 'The status change is pending approval from an Admin or Super Admin.',
        'changement_etat_approuve' => 'Status change approved.',
        'changement_etat_rejete' => 'Status change rejected.',
    ],
    'incident' => [
        'signale' => 'Incident reported.',
        'resolu' => 'Incident resolved.',
        'retour_traite' => 'Device return processed.',
        'relance_remplacement' => 'Employee notified to return the replacement device.',
    ],
    'alerte' => [
        'resolue' => 'Alert resolved.',
    ],
    'regle' => [
        'supprimee' => 'Alert rule deleted.',
    ],
    'prediction' => [
        'generee' => ':count prediction(s) generated.',
    ],
    'utilisateur' => [
        'cree' => 'User created.',
        'modifie' => 'User updated.',
        'supprime' => 'User deleted.',
        'auto_suppression' => 'You cannot delete your own account.',
    ],
];
