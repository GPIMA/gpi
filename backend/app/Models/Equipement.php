<?php

namespace App\Models;

use App\Enums\EtatEquipement;
use App\Enums\TypeEquipement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipement extends Model
{
    /** @use HasFactory<\Database\Factories\EquipementFactory> */
    use HasFactory;

    protected $fillable = [
        'nom', 'type', 'marque', 'modele', 'numero_serie', 'adresse_ip', 'adresse_mac',
        'etat', 'localisation', 'date_acquisition', 'technicien_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeEquipement::class,
            'etat' => EtatEquipement::class,
            'date_acquisition' => 'date',
        ];
    }

    public function estEnLigne(): bool
    {
        return $this->etat === EtatEquipement::EN_LIGNE;
    }

    // — Relations ————————————————————————————————————————————

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }

    /** Affectation active (non retournée), s'il y en a une. */
    public function affectationActive(): HasMany
    {
        return $this->affectations()->where('statut', 'EN_COURS');
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    /** Relevés de métriques (alimentent le moteur d'évaluation des règles d'alerte). */
    public function metriques(): HasMany
    {
        return $this->hasMany(Metrique::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /** Toutes les demandes de changement d'état soumises pour cet équipement. */
    public function demandesChangementEtat(): HasMany
    {
        return $this->hasMany(DemandeChangementEtat::class);
    }

    /** La demande de changement d'état encore en attente d'approbation, s'il y en a une. */
    public function demandeChangementEtatEnAttente(): HasMany
    {
        return $this->demandesChangementEtat()->where('statut', 'EN_ATTENTE');
    }
}
