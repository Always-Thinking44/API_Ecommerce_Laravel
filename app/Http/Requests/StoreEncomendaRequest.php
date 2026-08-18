<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEncomendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'endereco_entrega' => ['required', 'string', 'max:255'],
            'metodo_pagamento' => ['required', 'in:multicaixa,transferencia,cartao'],
        ];
    }
}
