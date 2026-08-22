<?php

namespace App\Models;

use App\Enums\EtatEquipement;
use App\Enums\StatutDemandeChangementEtat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandeChangementEtat extends Model
{
    protected $table = 'demandes_changement_etat';

    protected $fillable = [
        'equipement_id', 'demandeur_id', 'etat_actuel', 'etat_demande',
        'statut', 'motif', 'traite_par_id', 'traite_le', 'commentaire_traitement',
    ];

    protected function casts(): array
    {
        return [
            'etat_actuel' => EtatEquipement::class,
            'etat_demande' => EtatEquipement::class,
            'statut' => StatutDemandeChangementEtat::class,
            'traite_le' => 'datetime',
        ];
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    /** Technicien à l'origine de la demande. */
    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    /** Admin / Super Admin ayant approuvé ou rejeté la demande. */
    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par_id');
    }

    public function estEnAttente(): bool
    {
        return $this->statut === StatutDemandeChangementEtat::EN_ATTENTE;
    }

    /** Discussion libre entre le demandeur et l'Admin/Super Admin qui traite la demande. */
    public function commentaires(): HasMany
    {
        return $this->hasMany(DemandeChangementEtatCommentaire::class);
    }
}
