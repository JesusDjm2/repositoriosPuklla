<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kawsay: contenido que invita a interactuar.
 * - kawsay_archivos.descripcion / nivel: "para qué sirve" y "grado o nivel" de cada
 *   archivo (ayuda al docente a decidir si lo abre).
 * - kawsay_archivos.youtube_id: video por enlace de YouTube (calidad adaptable a la
 *   señal; drive_id queda vacío en esas filas).
 * (El nombre menciona "aportes" porque al inicio también creaba la tabla kawsay_aportes;
 *  esa función se descartó y la tabla se elimina en 2026_11_11_000002.)
 * Equivalente para producción: database/migracion_produccion_kawsay_20261111.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kawsay_archivos', function (Blueprint $table) {
            $table->string('descripcion', 500)->nullable()->after('titulo');
            $table->string('nivel', 100)->nullable()->after('descripcion');
            $table->string('youtube_id', 20)->nullable()->after('drive_id');
        });
    }

    public function down(): void
    {
        Schema::table('kawsay_archivos', fn (Blueprint $table) => $table->dropColumn(['descripcion', 'nivel', 'youtube_id']));
    }
};
