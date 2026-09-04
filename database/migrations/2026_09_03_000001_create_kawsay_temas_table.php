<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawsay_temas', function (Blueprint $table) {
            $table->id();
            $table->string('area', 50);
            $table->string('slug', 80);
            $table->string('titulo');
            $table->string('icono', 60)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['area', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawsay_temas');
    }
};
