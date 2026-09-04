<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prerrequisito para el campo "ubicación" (que se coloca `->after('tipo_material')`):
 * tipo_material no existía todavía en libros_fisicos. Nullable a nivel de BD
 * para no romper los registros existentes; se exige en el formulario (ver
 * LibroFisicoController).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libros_fisicos', function (Blueprint $table) {
            $table->string('tipo_material', 20)->nullable()->after('cantidad_ejemplares');
        });
    }

    public function down(): void
    {
        Schema::table('libros_fisicos', function (Blueprint $table) {
            $table->dropColumn('tipo_material');
        });
    }
};
