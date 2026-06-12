<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    /** @use HasFactory<\Database\Factories\AffectationFactory> */
    use HasFactory;

    protected $fillable = [
        'employe_id', 'equipement_id', 'date_affectation', 'date_retour', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_affectation' => 'date',
            'date_retour' => 'date',
        ];
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employe_id');
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }
}
