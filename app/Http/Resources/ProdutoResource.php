<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdutoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'preco' => (float) $this->preco,
            'stock' => $this->stock,
            'em_stock' => $this->stock > 0,
            'imagem' => $this->imagem,
            'ativo' => $this->ativo,
            'categoria' => new CategoriaResource($this->whenLoaded('categoria')),
            'media_avaliacoes' => $this->mediaAvaliacoes(),
            'total_avaliacoes' => $this->whenCounted('avaliacoes'),
            'created_at' => $this->created_at,
        ];
    }
}
