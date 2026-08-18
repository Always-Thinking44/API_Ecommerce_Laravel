<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarrinhoItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantidade' => $this->quantidade,
            'produto' => new ProdutoResource($this->whenLoaded('produto')),
            'subtotal' => $this->whenLoaded('produto', fn () => (float) $this->produto->preco * $this->quantidade),
        ];
    }
}
