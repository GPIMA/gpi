<?php

namespace App\Models;

use App\Enums\EtatAlerte;
use App\Enums\Severite;
use App\Enums\TypeAlerte;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alerte extends Model
{
    protected $fillable = [
        'equipement_id', 'regle_alerte_id', 'prediction_id',
        'type', 'severite', 'message', 'date_creation', 'etat', 'date_resolution',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeAlerte::class,
            'severite' => Severite::class,
            'etat' => EtatAlerte::class,
            'date_creation' => 'datetime',
            'date_resolution' => 'datetime',
        ];
    }

    public function resoudre(): void
    {
        $this->update([
            'etat' => EtatAlerte::RESOLUE,
            'date_resolution' => now(),
        ]);
    }

    // — Relations ————————————————————————————————————————————

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    public function regle(): BelongsTo
    {
        return $this->belongsTo(RegleAlerte::class, 'regle_alerte_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
