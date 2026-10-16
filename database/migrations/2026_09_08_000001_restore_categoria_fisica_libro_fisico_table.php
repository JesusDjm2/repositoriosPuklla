<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revierte la migración 2026_07_02_000002: la tabla pivote
 * categoria_fisica_libro_fisico se llegó a borrar en favor de una columna
 * única categoria_fisica_id en libros_fisicos, pero ningún modelo,
 * controlador ni vista se actualizó para dejar de usar la relación N:M —
 * LibroFisico::categoriasFisicas() y CategoriaFisica::librosFisicos() siguen
 * apuntando a la tabla pivote, así que su ausencia rompía (500) cualquier
 * página que las tocara (portada de la Escuela, catálogo físico, alta/edición
 * de libros físicos). Se recrea la tabla y se repuebla desde
 * categoria_fisica_id (que sí quedó con los datos correctos), y luego se
 * quita esa columna por quedar redundante con la tabla pivote restaurada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_fisica_libro_fisico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_fisica_id')->constrained('categorias_fisicas')->cascadeOnDelete();
            $table->foreignId('libro_fisico_id')->constrained('libros_fisicos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['categoria_fisica_id', 'libro_fisico_id'], 'catfis_libfis_unique');
        });

        DB::statement('
            INSERT INTO categoria_fisica_libro_fisico (categoria_fisica_id, libro_fisico_id, created_at, updated_at)
            SELECT categoria_fisica_id, id, NOW(), NOW()
            FROM libros_fisicos
            WHERE categoria_fisica_id IS NOT NULL
        ');

        Schema::table('libros_fisicos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_fisica_id');
        });
    }

    public function down(): void
    {
        Schema::table('libros_fisicos', function (Blueprint $table) {
            $table->foreignId('categoria_fisica_id')->nullable()->after('id')
                ->constrained('categorias_fisicas')->nullOnDelete();
        });

        DB::statement('
            UPDATE libros_fisicos lf
            JOIN categoria_fisica_libro_fisico p ON p.libro_fisico_id = lf.id
            SET lf.categoria_fisica_id = p.categoria_fisica_id
        ');

        Schema::dropIfExists('categoria_fisica_libro_fisico');
    }
};
