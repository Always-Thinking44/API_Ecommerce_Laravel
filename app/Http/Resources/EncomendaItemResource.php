<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncomendaItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantidade' => $this->quantidade,
            'preco_unitario' => (float) $this->preco_unitario,
            'subtotal' => (float) $this->preco_unitario * $this->quantidade,
            'produto' => new ProdutoResource($this->whenLoaded('produto')),
        ];
    }
}
