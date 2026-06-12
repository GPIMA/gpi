<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metrique extends Model
{
    /** @use HasFactory<\Database\Factories\MetriqueFactory> */
    use HasFactory;

    protected $fillable = ['equipement_id', 'date_heure', 'cpu_usage', 'ram_usage', 'disk_usage'];

    protected function casts(): array
    {
        return [
            'date_heure' => 'datetime',
            'cpu_usage' => 'float',
            'ram_usage' => 'float',
            'disk_usage' => 'float',
        ];
    }

    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    /** Valeur d'une cible de règle (cpu|ram|disque) sur ce relevé. */
    public function valeurCible(string $cible): ?float
    {
        return match ($cible) {
            'cpu' => $this->cpu_usage,
            'ram' => $this->ram_usage,
            'disque' => $this->disk_usage,
            default => null,
        };
    }
}
