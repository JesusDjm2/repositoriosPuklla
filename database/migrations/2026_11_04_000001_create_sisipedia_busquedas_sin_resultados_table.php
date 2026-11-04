<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Términos buscados en Sisipedia que no devolvieron resultados (ver BusquedaController). */
    public function up(): void
    {
        Schema::create('sisipedia_busquedas_sin_resultados', function (Blueprint $table) {
            $table->id();
            $table->string('termino', 120)->unique();
            $table->unsignedInteger('veces')->default(1);
            $table->timestamp('ultima_vez')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sisipedia_busquedas_sin_resultados');
    }
};
