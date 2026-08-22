<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeChangementEtatCommentaire extends Model
{
    protected $fillable = ['demande_changement_etat_id', 'auteur_id', 'contenu'];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeChangementEtat::class, 'demande_changement_etat_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
