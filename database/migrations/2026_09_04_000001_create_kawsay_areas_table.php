<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawsay_areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('nombre');
            $table->string('icono', 60)->nullable();
            $table->string('color', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        // Las 4 áreas que hoy viven a mano en KawsayTaxonomia::areas(), como
        // primeros registros reales — mismos datos, mismas imágenes ya
        // subidas en public/img/kawsay/, para no perder nada al migrar.
        DB::table('kawsay_areas')->insert([
            [
                'slug' => 'territorio-vivo',
                'nombre' => 'Territorio Vivo',
                'icono' => 'fa-mountain',
                'color' => '#474f2e',
                'descripcion' => 'Reconocemos y cuidamos los elementos que sostienen la vida en nuestro territorio.',
                'imagen' => 'img/kawsay/territorio-vivo.webp',
                'orden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'expresion-biocultural',
                'nombre' => 'Expresión Biocultural',
                'icono' => 'fa-drum',
                'color' => '#8d4d27',
                'descripcion' => 'Valoramos nuestras culturas, saberes y formas de vida que dan identidad y sentido.',
                'imagen' => 'img/kawsay/Biocultura.jpg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'gestion-sostenible',
                'nombre' => 'Gestión Sostenible del Territorio',
                'icono' => 'fa-recycle',
                'color' => '#232c15',
                'descripcion' => 'Usamos responsablemente los recursos, reducimos impactos y generamos soluciones para el bien común.',
                'imagen' => 'img/kawsay/gestion-sostenible.jpg',
                'orden' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'resiliencia-socioecologica',
                'nombre' => 'Resiliencia Socioecológica y Biocultural',
                'icono' => 'fa-users',
                'color' => '#384e55',
                'descripcion' => 'Fortalecemos nuestra capacidad de adaptarnos, regenerarnos y construir un futuro justo y sostenible.',
                'imagen' => 'img/kawsay/resilencia.webp',
                'orden' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kawsay_areas');
    }
};
