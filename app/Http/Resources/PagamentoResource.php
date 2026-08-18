<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'metodo' => $this->metodo,
            'estado' => $this->estado,
            'valor' => (float) $this->valor,
            'referencia' => $this->referencia,
        ];
    }
}
