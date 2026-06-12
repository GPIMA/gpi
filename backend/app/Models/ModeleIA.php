<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeleIA extends Model
{
    protected $table = 'modele_ias';

    protected $fillable = ['nom', 'algorithme', 'date_entrainement', 'precision', 'version'];

    protected function casts(): array
    {
        return [
            'date_entrainement' => 'datetime',
            'precision' => 'float',
        ];
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }
}
