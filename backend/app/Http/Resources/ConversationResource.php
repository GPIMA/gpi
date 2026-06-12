<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Conversation
 */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'dateDebut' => $this->date_debut,
            'dateFin' => $this->date_fin,
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'dernierMessage' => $this->whenLoaded('messages', fn () => optional($this->messages->last())->contenu),
        ];
    }
}
