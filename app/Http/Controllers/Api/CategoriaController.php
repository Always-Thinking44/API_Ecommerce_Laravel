<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('produtos')->orderBy('nome')->get();

        return CategoriaResource::collection($categorias);
    }

    public function show(Categoria $categoria)
    {
        $categoria->loadCount('produtos');

        return new CategoriaResource($categoria);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
        ]);

        $data['slug'] = Str::slug($data['nome']);

        $categoria = Categoria::create($data);

        return new CategoriaResource($categoria);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
        ]);

        if (isset($data['nome'])) {
            $data['slug'] = Str::slug($data['nome']);
        }

        $categoria->update($data);

        return new CategoriaResource($categoria);
    }

    public function destroy(Request $request, Categoria $categoria)
    {
        $this->authorizeAdmin($request);

        $categoria->delete();

        return response()->json(null, 204);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Apenas administradores podem gerir categorias.');
    }
}
