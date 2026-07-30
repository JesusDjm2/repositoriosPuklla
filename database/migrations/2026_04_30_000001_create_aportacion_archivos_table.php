<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aportacion_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('aportacion_id')
                ->constrained('aportaciones')
                ->cascadeOnDelete();

            $table->string('tipo', 20);                                // imagen, pdf, doc, audio, video
            $table->string('drive_id', 255);                           // ID en Google Drive
            $table->string('nombre_original', 255)->nullable();        // nombre del archivo subido
            $table->decimal('peso_mb', 10, 2)->unsigned()->nullable(); // peso en MB
            $table->timestamps();

            // Índices
            $table->index('tipo');
            $table->index('aportacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aportacion_archivos');
    }
};
