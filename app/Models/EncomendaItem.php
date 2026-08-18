<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncomendaItem extends Model
{
    protected $table = 'encomenda_itens';

    protected $fillable = ['encomenda_id', 'produto_id', 'quantidade', 'preco_unitario'];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
    ];

    public function encomenda(): BelongsTo
    {
        return $this->belongsTo(Encomenda::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
