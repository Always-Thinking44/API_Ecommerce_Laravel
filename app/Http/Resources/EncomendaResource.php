<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncomendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total' => (float) $this->total,
            'estado' => $this->estado,
            'endereco_entrega' => $this->endereco_entrega,
            'itens' => EncomendaItemResource::collection($this->whenLoaded('itens')),
            'pagamento' => new PagamentoResource($this->whenLoaded('pagamento')),
            'created_at' => $this->created_at,
        ];
    }
}
