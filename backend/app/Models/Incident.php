<?php

namespace App\Models;

use App\Enums\Severite;
use App\Enums\StatutIncident;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'equipement_id', 'employe_id', 'technicien_id', 'titre', 'description',
        'date_signalement', 'date_restitution_prevue' , 'date_resolution', 'statut', 'priorite', 'solution', 'piece_jointe',
        'equipement_remplacement_id', 'relance_remplacement_le', 'date_reception_poste',
    ];

    protected function casts(): array
    {
        return [
            'date_restitution_prevue' => 'datetime',
            'date_signalement' => 'datetime',
            'date_resolution' => 'datetime',
            'relance_remplacement_le' => 'datetime',
            'date_reception_poste' => 'datetime',
            'statut' => StatutIncident::class,
            'priorite' => Severite::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Incident $incident) {
            if (! $incident->reference) {
                $incident->update([
                    'reference' => 'INC-'.str_pad((string) $incident->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    /** Prise en charge par un technicien (traiterIncident du diagramme). */
    public function prendreEnCharge(User $technicien): void
    {
        $this->update([
            'technicien_id' => $technicien->id,
            'statut' => StatutIncident::EN_COURS,
        ]);
    }

    /** Résolution (resoudre(solution) du diagramme). */
    public function resoudre(User $technicien, string $solution): void
    {
        $this->update([
            'technicien_id' => $this->technicien_id ?? $technicien->id,
            'statut' => StatutIncident::RESOLU,
            'solution' => $solution,
            'date_resolution' => now(),
        ]);
    }

    /** Demande à l'employé de ramener le poste. */
    public function demanderRestitution(User $technicien, \Carbon\Carbon $date): void
    {
        $this->update([
            'technicien_id' => $this->technicien_id ?? $technicien->id,
            'statut' => StatutIncident::EN_COURS,
            'date_restitution_prevue' => $date,
            // Nouvelle demande de restitution : on repart d'une phase "poste
            // pas encore reçu" (utile si l'incident a déjà connu un premier
            // cycle ramener/recevoir avant celui-ci).
            'date_reception_poste' => null,
        ]);
    }
/** Réouverture par l'employé si le problème persiste (fenêtre de 5 jours après résolution). */
    public function reouvrir(): void
    {
        $this->update([
            'statut' => StatutIncident::EN_COURS,
        ]);
    }
    // — Relations ————————————————————————————————————————————

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    /** Poste remplaçant temporaire (motif "Nouvelle date"), tant qu'il n'a pas été rendu. */
    public function equipementRemplacement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class, 'equipement_remplacement_id');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employe_id');
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(IncidentCommentaire::class)->orderBy('created_at');
    }


    public function piecesJointes(): HasMany
    {
        return $this->hasMany(IncidentPieceJointe::class);
    }
}