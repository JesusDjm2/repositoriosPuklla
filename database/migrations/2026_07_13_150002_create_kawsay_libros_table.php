<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawsay_libros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('autor');
            $table->string('identificador')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawsay_libros');
    }
};
