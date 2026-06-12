<?php

namespace App\Models;

use App\Enums\ExpediteurType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'contenu', 'expediteur', 'date_envoi'];

    protected function casts(): array
    {
        return [
            'expediteur' => ExpediteurType::class,
            'date_envoi' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
