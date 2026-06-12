<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Message
 */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contenu' => $this->contenu,
            'expediteur' => $this->expediteur->value,
            'estChatbot' => $this->expediteur === \App\Enums\ExpediteurType::CHATBOT,
            'dateEnvoi' => $this->date_envoi,
        ];
    }
}
