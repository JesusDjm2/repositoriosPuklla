<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Se descarta "Comparte tu experiencia" (envíos de visitantes): los visitantes
 * interactúan con los registros existentes y con los comentarios.
 * Equivalente para producción: database/migracion_produccion_kawsay_quitar_aportes_20261111.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('kawsay_aportes');
    }

    public function down(): void
    {
        // Sin vuelta atrás: la función se retiró del código.
    }
};
