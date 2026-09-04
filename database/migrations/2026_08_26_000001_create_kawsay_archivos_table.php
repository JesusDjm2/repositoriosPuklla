<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawsay_archivos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('titulo');
            $table->string('drive_id');
            $table->string('nombre_original');
            $table->decimal('peso_mb', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawsay_archivos');
    }
};
