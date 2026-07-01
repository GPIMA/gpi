<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeInscription extends Model
{
    protected $table = 'demandes_inscription';

    protected $fillable = [
    'nom', 'prenom', 'email', 'role', 'telephone', 'departement', 'message', 'statut',
];
}