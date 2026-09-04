<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawsay_tema_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kawsay_tema_id')->constrained('kawsay_temas')->cascadeOnDelete();
            $table->string('imagen');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawsay_tema_imagenes');
    }
};
