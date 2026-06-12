<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Metrique
 */
class MetriqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dateHeure' => $this->date_heure,
            'cpu' => $this->cpu_usage,
            'ram' => $this->ram_usage,
            'disque' => $this->disk_usage,
        ];
    }
}
