<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvaliacaoController;
use App\Http\Controllers\Api\CarrinhoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EncomendaController;
use App\Http\Controllers\Api\ProdutoController;
use Illuminate\Support\Facades\Route;

// ---------- Público ----------
Route::post('/registo', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{categoria}', [CategoriaController::class, 'show']);

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/{produto}', [ProdutoController::class, 'show']);
Route::get('/produtos/{produto}/avaliacoes', [AvaliacaoController::class, 'index']);

// ---------- Autenticado (Sanctum) ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Carrinho
    Route::get('/carrinho', [CarrinhoController::class, 'index']);
    Route::post('/carrinho', [CarrinhoController::class, 'store']);
    Route::patch('/carrinho/{item}', [CarrinhoController::class, 'update']);
    Route::delete('/carrinho/{item}', [CarrinhoController::class, 'destroy']);

    // Encomendas
    Route::get('/encomendas', [EncomendaController::class, 'index']);
    Route::post('/encomendas', [EncomendaController::class, 'store']);
    Route::get('/encomendas/{encomenda}', [EncomendaController::class, 'show']);
    Route::patch('/encomendas/{encomenda}/estado', [EncomendaController::class, 'atualizarEstado']); // admin

    // Avaliações
    Route::post('/produtos/{produto}/avaliacoes', [AvaliacaoController::class, 'store']);
    Route::delete('/produtos/{produto}/avaliacoes/{avaliacaoId}', [AvaliacaoController::class, 'destroy']);

    // Gestão de produtos/categorias (admin — autorização feita dentro do controller)
    Route::post('/produtos', [ProdutoController::class, 'store']);
    Route::put('/produtos/{produto}', [ProdutoController::class, 'update']);
    Route::patch('/produtos/{produto}', [ProdutoController::class, 'update']);
    Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy']);

    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update']);
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy']);
});
