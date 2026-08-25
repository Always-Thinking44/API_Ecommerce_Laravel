<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProdutoController extends Controller
{
    // Listagem pública, com filtros: ?categoria=slug&busca=texto&ordenar=preco_asc
    public function index(Request $request)
    {
        $query = Produto::query()->with('categoria')->withCount('avaliacoes')->where('ativo', true);

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', fn ($q) => $q->where('slug', $request->string('categoria')));
        }

        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->string('busca') . '%');
        }

        match ($request->string('ordenar')->toString()) {
            'preco_asc' => $query->orderBy('preco'),
            'preco_desc' => $query->orderByDesc('preco'),
            'recentes' => $query->orderByDesc('created_at'),
            default => $query->orderBy('nome'),
        };

        $produtos = $query->paginate(12);

        return ProdutoResource::collection($produtos);
    }

    public function show(Produto $produto)
    {
        $produto->load('categoria')->loadCount('avaliacoes');

        return new ProdutoResource($produto);
    }

    public function store(StoreProdutoRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['nome']) . '-' . uniqid();

        if ($request->hasFile('imagem')) {
            $data['imagem'] = $request->file('imagem')->store('produtos', 'public');
        }

        $produto = Produto::create($data);

        return new ProdutoResource($produto->load('categoria'));
    }

    public function update(UpdateProdutoRequest $request, Produto $produto)
    {
        $data = $request->validated();

        if (isset($data['nome'])) {
            $data['slug'] = Str::slug($data['nome']) . '-' . uniqid();
        }

        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $data['imagem'] = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->update($data);

        return new ProdutoResource($produto->load('categoria'));
    }

    public function destroy(Request $request, Produto $produto)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $produto->delete();

        return response()->json(null, 204);
    }
}
