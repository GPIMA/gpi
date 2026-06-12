<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sujet' => $this->sujet,
            'contenu' => $this->contenu,
            'canal' => $this->canal->value,
            'canalLabel' => $this->canal->label(),
            'statut' => $this->statut,
            'lue' => $this->statut === 'LUE',
            'dateEnvoi' => $this->date_envoi,
        ];
    }
}
