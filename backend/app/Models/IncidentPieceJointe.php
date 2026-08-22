<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentPieceJointe extends Model
{
    protected $table = 'incident_pieces_jointes';
    protected $fillable = ['incident_id', 'chemin', 'nom_original'];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}