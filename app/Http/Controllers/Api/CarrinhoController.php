<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrinhoItemRequest;
use App\Http\Resources\CarrinhoItemResource;
use App\Models\CarrinhoItem;
use App\Models\Produto;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    // GET /api/carrinho
    public function index(Request $request)
    {
        $itens = $request->user()
            ->carrinhoItens()
            ->with('produto.categoria')
            ->get();

        return response()->json([
            'itens' => CarrinhoItemResource::collection($itens),
            'total' => $itens->sum(fn (CarrinhoItem $item) => $item->produto->preco * $item->quantidade),
        ]);
    }

    // POST /api/carrinho — adiciona produto (ou soma quantidade se já existir)
    public function store(StoreCarrinhoItemRequest $request)
    {
        $data = $request->validated();
        $produto = Produto::findOrFail($data['produto_id']);

        abort_if($produto->stock < $data['quantidade'], 422, 'Stock insuficiente para este produto.');

        $item = CarrinhoItem::where('user_id', $request->user()->id)
            ->where('produto_id', $produto->id)
            ->first();

        if ($item) {
            $item->increment('quantidade', $data['quantidade']);
        } else {
            $item = CarrinhoItem::create([
                'user_id' => $request->user()->id,
                'produto_id' => $produto->id,
                'quantidade' => $data['quantidade'],
            ]);
        }

        return new CarrinhoItemResource($item->load('produto.categoria'));
    }

    // PATCH /api/carrinho/{item}
    public function update(Request $request, CarrinhoItem $item)
    {
        abort_unless($item->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
        ]);

        abort_if($item->produto->stock < $data['quantidade'], 422, 'Stock insuficiente para este produto.');

        $item->update($data);

        return new CarrinhoItemResource($item->load('produto.categoria'));
    }

    // DELETE /api/carrinho/{item}
    public function destroy(Request $request, CarrinhoItem $item)
    {
        abort_unless($item->user_id === $request->user()->id, 403);

        $item->delete();

        return response()->json(null, 204);
    }
}
