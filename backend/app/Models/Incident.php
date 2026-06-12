<?php

namespace App\Models;

use App\Enums\Severite;
use App\Enums\StatutIncident;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentFactory> */
    use HasFactory;

    protected $fillable = [
        'equipement_id', 'employe_id', 'technicien_id', 'titre', 'description',
        'date_signalement', 'date_resolution', 'statut', 'priorite', 'solution',
    ];

    protected function casts(): array
    {
        return [
            'date_signalement' => 'datetime',
            'date_resolution' => 'datetime',
            'statut' => StatutIncident::class,
            'priorite' => Severite::class,
        ];
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

    // — Relations ————————————————————————————————————————————

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employe_id');
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}
