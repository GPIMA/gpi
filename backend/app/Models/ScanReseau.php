<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScanReseau extends Model
{
    protected $table = 'scan_reseaux';

    protected $fillable = ['plage_ip', 'date_scan', 'duree', 'nb_detectes', 'lance_par'];

    protected function casts(): array
    {
        return ['date_scan' => 'datetime'];
    }

    /** Équipements détectés par ce scan. */
    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class);
    }

    public function operateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lance_par');
    }
}
