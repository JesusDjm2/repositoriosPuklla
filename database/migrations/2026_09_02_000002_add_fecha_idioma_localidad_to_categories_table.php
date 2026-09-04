<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('description');
            $table->string('idioma', 20)->nullable()->after('fecha');
            $table->string('localidad')->nullable()->after('idioma');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['fecha', 'idioma', 'localidad']);
        });
    }
};
