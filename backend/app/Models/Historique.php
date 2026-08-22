<?php
// app/Models/Historique.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    protected $fillable = [
        'user_id', 'auteur_id', 'equipement_id', 'incident_id', 'action', 'description',
        'donnees_avant', 'donnees_apres',
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Membre du staff (admin/technicien) qui a réalisé l'action. */
    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}