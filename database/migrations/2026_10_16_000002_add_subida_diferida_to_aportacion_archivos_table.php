<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite subir el archivo a Google Drive en segundo plano (cola) en vez de
     * dentro de la misma petición del formulario de aportación: con internet lento,
     * re-subir un video grande a Drive de forma síncrona agota el timeout del
     * proxy del hosting (504 Gateway Timeout) aunque PHP todavía tenga margen.
     *
     * drive_id se pasa a nullable con SQL crudo (no ->change()) porque el proyecto
     * no tiene doctrine/dbal instalado, requisito de ese método en producción.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE aportacion_archivos MODIFY drive_id VARCHAR(255) NULL');

        Schema::table('aportacion_archivos', function (Blueprint $table) {
            $table->string('ruta_local')->nullable()->after('drive_id');
            $table->timestamp('fallo_en')->nullable()->after('ruta_local');
        });
    }

    public function down(): void
    {
        Schema::table('aportacion_archivos', function (Blueprint $table) {
            $table->dropColumn(['ruta_local', 'fallo_en']);
        });

        DB::statement('ALTER TABLE aportacion_archivos MODIFY drive_id VARCHAR(255) NOT NULL');
    }
};
