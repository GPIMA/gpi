<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'titre', 'date_debut', 'date_fin'];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
        ];
    }

    public function ajouterMessage(string $contenu, \App\Enums\ExpediteurType $expediteur): Message
    {
        return $this->messages()->create([
            'contenu' => $contenu,
            'expediteur' => $expediteur,
            'date_envoi' => now(),
        ]);
    }

    public function cloturer(): void
    {
        $this->update(['date_fin' => now()]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('date_envoi');
    }
}
