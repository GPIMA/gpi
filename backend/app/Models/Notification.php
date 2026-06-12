<?php

namespace App\Models;

use App\Enums\CanalNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'destinataire_id', 'alerte_id', 'sujet', 'contenu', 'canal', 'date_envoi', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'canal' => CanalNotification::class,
            'date_envoi' => 'datetime',
        ];
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function alerte(): BelongsTo
    {
        return $this->belongsTo(Alerte::class);
    }
}
