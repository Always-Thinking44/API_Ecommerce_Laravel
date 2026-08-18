<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvaliacaoRequest;
use App\Http\Resources\AvaliacaoResource;
use App\Models\Produto;
use Illuminate\Http\Request;

class AvaliacaoController extends Controller
{
    // GET /api/produtos/{produto}/avaliacoes
    public function index(Produto $produto)
    {
        $avaliacoes = $produto->avaliacoes()->with('user')->orderByDesc('created_at')->get();

        return AvaliacaoResource::collection($avaliacoes);
    }

    // POST /api/produtos/{produto}/avaliacoes
    public function store(StoreAvaliacaoRequest $request, Produto $produto)
    {
        $existente = $produto->avaliacoes()->where('user_id', $request->user()->id)->exists();

        abort_if($existente, 422, 'Já avaliaste este produto.');

        $avaliacao = $produto->avaliacoes()->create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return new AvaliacaoResource($avaliacao->load('user'));
    }

    public function destroy(Request $request, Produto $produto, int $avaliacaoId)
    {
        $avaliacao = $produto->avaliacoes()->findOrFail($avaliacaoId);

        abort_unless(
            $avaliacao->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $avaliacao->delete();

        return response()->json(null, 204);
    }
}
