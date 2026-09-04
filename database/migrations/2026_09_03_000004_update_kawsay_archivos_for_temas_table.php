<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kawsay_archivos', function (Blueprint $table) {
            $table->dropColumn(['area', 'tema']);
            $table->foreignId('kawsay_tema_id')->nullable()->after('id')->constrained('kawsay_temas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kawsay_archivos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kawsay_tema_id');
            $table->string('area', 50)->nullable()->after('id');
            $table->string('tema', 50)->nullable()->after('area');
        });
    }
};
