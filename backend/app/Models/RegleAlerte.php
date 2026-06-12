<?php

namespace App\Models;

use App\Enums\Severite;
use App\Enums\TypeAlerte;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegleAlerte extends Model
{
    protected $fillable = ['nom', 'metrique_cible', 'operateur', 'seuil', 'severite', 'type_alerte', 'actif'];

    protected function casts(): array
    {
        return [
            'seuil' => 'float',
            'actif' => 'boolean',
            'severite' => Severite::class,
            'type_alerte' => TypeAlerte::class,
        ];
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    /** Évalue la règle contre une métrique : seuil franchi ? */
    public function evaluer(Metrique $metrique): bool
    {
        $valeur = $metrique->valeurCible($this->metrique_cible);
        if ($valeur === null) {
            return false;
        }

        return match ($this->operateur) {
            '>' => $valeur > $this->seuil,
            '>=' => $valeur >= $this->seuil,
            '<' => $valeur < $this->seuil,
            '<=' => $valeur <= $this->seuil,
            default => false,
        };
    }
}
