<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_id')->unique()->constrained('encomendas')->cascadeOnDelete();
            $table->enum('metodo', ['multicaixa', 'transferencia', 'cartao'])->default('multicaixa');
            $table->enum('estado', ['pendente', 'aprovado', 'recusado'])->default('pendente');
            $table->decimal('valor', 10, 2);
            $table->string('referencia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
