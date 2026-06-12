<?php

namespace App\Models;

use App\Enums\TypeAlerte;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prediction extends Model
{
    protected $fillable = [
        'modele_ia_id', 'equipement_id', 'date_generation', 'type_panne', 'probabilite', 'horizon_jours',
    ];

    protected function casts(): array
    {
        return [
            'date_generation' => 'datetime',
            'type_panne' => TypeAlerte::class,
            'probabilite' => 'float',
            'horizon_jours' => 'integer',
        ];
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    public function modele(): BelongsTo
    {
        return $this->belongsTo(ModeleIA::class, 'modele_ia_id');
    }

    public function alerte(): HasOne
    {
        return $this->hasOne(Alerte::class);
    }
}
