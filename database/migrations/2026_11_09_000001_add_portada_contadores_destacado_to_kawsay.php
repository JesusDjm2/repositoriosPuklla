<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kawsay más visual y medible:
 * - kawsay_archivos.portada: imagen propia para un video (Drive entrega el primer
 *   fotograma, casi siempre negro). Ruta relativa a public/.
 * - kawsay_archivos.vistas / kawsay_temas.visitas: contadores (una vez por sesión).
 * - kawsay_temas.destacado: "Tema del mes" de la portada (solo uno a la vez).
 * - kawsay_libros / kawsay_videos.kawsay_tema_id: el catálogo antiguo se puede
 *   asignar a un tema y aparece dentro de él.
 * Equivalente para producción: database/migracion_produccion_kawsay_20261109.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kawsay_archivos', function (Blueprint $table) {
            $table->string('portada')->nullable()->after('peso_mb');
            $table->unsignedInteger('vistas')->default(0)->after('portada');
        });
        Schema::table('kawsay_temas', function (Blueprint $table) {
            $table->unsignedInteger('visitas')->default(0)->after('orden');
            $table->boolean('destacado')->default(false)->after('visitas');
        });
        Schema::table('kawsay_libros', function (Blueprint $table) {
            $table->foreignId('kawsay_tema_id')->nullable()->after('identificador')
                ->constrained('kawsay_temas')->nullOnDelete();
        });
        Schema::table('kawsay_videos', function (Blueprint $table) {
            $table->foreignId('kawsay_tema_id')->nullable()->after('categoria_id')
                ->constrained('kawsay_temas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kawsay_videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kawsay_tema_id');
        });
        Schema::table('kawsay_libros', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kawsay_tema_id');
        });
        Schema::table('kawsay_temas', fn (Blueprint $table) => $table->dropColumn(['visitas', 'destacado']));
        Schema::table('kawsay_archivos', fn (Blueprint $table) => $table->dropColumn(['portada', 'vistas']));
    }
};
