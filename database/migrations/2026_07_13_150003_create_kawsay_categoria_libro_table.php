<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawsay_categoria_libro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kawsay_categoria_id')->constrained('kawsay_categorias')->onDelete('cascade');
            $table->foreignId('kawsay_libro_id')->constrained('kawsay_libros')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawsay_categoria_libro');
    }
};
