<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contadores de Sisipedia: visitas de cada registro y descargas de cada archivo
 * (materiales del registro y archivos de aportes). El total de descargas de un
 * registro se calcula sumando sus archivos, así no hay dos números que cuadrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('visitas')->default(0)->after('is_active');
        });
        Schema::table('category_files', function (Blueprint $table) {
            $table->unsignedInteger('descargas')->default(0)->after('orden');
        });
        Schema::table('aportacion_archivos', function (Blueprint $table) {
            $table->unsignedInteger('descargas')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('visitas'));
        Schema::table('category_files', fn (Blueprint $table) => $table->dropColumn('descargas'));
        Schema::table('aportacion_archivos', fn (Blueprint $table) => $table->dropColumn('descargas'));
    }
};
