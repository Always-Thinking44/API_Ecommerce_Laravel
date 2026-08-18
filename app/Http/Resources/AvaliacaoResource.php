<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvaliacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nota' => $this->nota,
            'comentario' => $this->comentario,
            'utilizador' => $this->whenLoaded('user', fn () => $this->user->name),
            'created_at' => $this->created_at,
        ];
    }
}
