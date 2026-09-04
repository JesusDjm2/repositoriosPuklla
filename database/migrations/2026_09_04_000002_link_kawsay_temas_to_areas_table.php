<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kawsay_temas', function (Blueprint $table) {
            $table->dropUnique('kawsay_temas_area_slug_unique');
            $table->foreignId('kawsay_area_id')->nullable()->after('id')->constrained('kawsay_areas')->cascadeOnDelete();
        });

        // Reasigna cada tema existente a su área real por slug (los temas ya
        // migrados de "Gestión Sostenible del Territorio" tenían area="gestion-sostenible").
        DB::table('kawsay_temas')
            ->join('kawsay_areas', 'kawsay_areas.slug', '=', 'kawsay_temas.area')
            ->update(['kawsay_temas.kawsay_area_id' => DB::raw('kawsay_areas.id')]);

        Schema::table('kawsay_temas', function (Blueprint $table) {
            $table->dropColumn('area');
            $table->unique(['kawsay_area_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('kawsay_temas', function (Blueprint $table) {
            $table->dropUnique(['kawsay_area_id', 'slug']);
            $table->string('area', 50)->nullable()->after('id');
        });

        DB::table('kawsay_temas')
            ->join('kawsay_areas', 'kawsay_areas.id', '=', 'kawsay_temas.kawsay_area_id')
            ->update(['kawsay_temas.area' => DB::raw('kawsay_areas.slug')]);

        Schema::table('kawsay_temas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kawsay_area_id');
            $table->unique(['area', 'slug']);
        });
    }
};
