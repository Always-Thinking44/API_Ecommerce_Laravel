<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEncomendaRequest;
use App\Http\Resources\EncomendaResource;
use App\Models\Encomenda;
use App\Models\EncomendaItem;
use App\Models\Pagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncomendaController extends Controller
{
    // GET /api/encomendas — histórico do próprio utilizador
    public function index(Request $request)
    {
        $encomendas = $request->user()
            ->encomendas()
            ->with(['itens.produto', 'pagamento'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return EncomendaResource::collection($encomendas);
    }

    public function show(Request $request, Encomenda $encomenda)
    {
        abort_unless(
            $encomenda->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        return new EncomendaResource($encomenda->load(['itens.produto', 'pagamento']));
    }

    // POST /api/encomendas — transforma o carrinho atual numa encomenda
    public function store(StoreEncomendaRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $carrinho = $user->carrinhoItens()->with('produto')->get();

        abort_if($carrinho->isEmpty(), 422, 'O carrinho está vazio.');

        foreach ($carrinho as $item) {
            abort_if(
                $item->produto->stock < $item->quantidade,
                422,
                "Stock insuficiente para o produto {$item->produto->nome}."
            );
        }

        $encomenda = DB::transaction(function () use ($user, $carrinho, $data) {
            $total = $carrinho->sum(fn ($item) => $item->produto->preco * $item->quantidade);

            $encomenda = Encomenda::create([
                'user_id' => $user->id,
                'total' => $total,
                'estado' => 'pendente',
                'endereco_entrega' => $data['endereco_entrega'],
            ]);

            foreach ($carrinho as $item) {
                EncomendaItem::create([
                    'encomenda_id' => $encomenda->id,
                    'produto_id' => $item->produto_id,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->produto->preco,
                ]);

                // reserva o stock
                $item->produto->decrement('stock', $item->quantidade);
            }

            Pagamento::create([
                'encomenda_id' => $encomenda->id,
                'metodo' => $data['metodo_pagamento'],
                'estado' => 'pendente',
                'valor' => $total,
            ]);

            // esvazia o carrinho
            $user->carrinhoItens()->delete();

            return $encomenda;
        });

        return new EncomendaResource($encomenda->load(['itens.produto', 'pagamento']));
    }

    // PATCH /api/encomendas/{encomenda}/estado — só admin
    public function atualizarEstado(Request $request, Encomenda $encomenda)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'estado' => ['required', 'in:pendente,paga,enviada,entregue,cancelada'],
        ]);

        $encomenda->update($data);

        return new EncomendaResource($encomenda->load(['itens.produto', 'pagamento']));
    }
}
