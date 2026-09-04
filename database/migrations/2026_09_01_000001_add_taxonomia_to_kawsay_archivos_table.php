<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kawsay_archivos', function (Blueprint $table) {
            $table->string('area', 50)->nullable()->after('id');
            $table->string('tema', 50)->nullable()->after('area');
            $table->string('tipo_contenido', 30)->nullable()->after('tema');
        });
    }

    public function down(): void
    {
        Schema::table('kawsay_archivos', function (Blueprint $table) {
            $table->dropColumn(['area', 'tema', 'tipo_contenido']);
        });
    }
};
