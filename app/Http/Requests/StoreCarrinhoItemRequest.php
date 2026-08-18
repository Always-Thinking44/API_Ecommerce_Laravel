<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarrinhoItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // qualquer utilizador autenticado (garantido pelo middleware auth:sanctum)
    }

    public function rules(): array
    {
        return [
            'produto_id' => ['required', 'exists:produtos,id'],
            'quantidade' => ['required', 'integer', 'min:1'],
        ];
    }
}
