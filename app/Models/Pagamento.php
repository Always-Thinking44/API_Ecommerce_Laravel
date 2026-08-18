<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $fillable = ['encomenda_id', 'metodo', 'estado', 'valor', 'referencia'];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function encomenda(): BelongsTo
    {
        return $this->belongsTo(Encomenda::class);
    }
}
