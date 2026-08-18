<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id', 'nome', 'slug', 'descricao', 'preco', 'stock', 'imagem', 'ativo',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function carrinhoItens(): HasMany
    {
        return $this->hasMany(CarrinhoItem::class);
    }

    public function encomendaItens(): HasMany
    {
        return $this->hasMany(EncomendaItem::class);
    }

    public function mediaAvaliacoes(): float
    {
        return round($this->avaliacoes()->avg('nota') ?? 0, 1);
    }
}
