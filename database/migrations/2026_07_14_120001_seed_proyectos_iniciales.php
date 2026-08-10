<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Proyecto;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $biblioteca = Role::firstOrCreate(['name' => 'biblioteca']);
        $sisicha = Role::firstOrCreate(['name' => 'sisicha']);
        $videos = Role::firstOrCreate(['name' => 'videos']);
        $audios = Role::firstOrCreate(['name' => 'audios']);
        $kawsay = Role::firstOrCreate(['name' => 'kawsay']);

        $eesp = Proyecto::firstOrCreate(
            ['slug' => 'eesp-pukllasunchis'],
            [
                'nombre' => 'EESP Pukllasunchis',
                'logo' => 'logo-iesp-pukllasunchis.png',
                'orden' => 1,
                'activo' => true,
            ]
        );
        $eesp->roles()->syncWithoutDetaching([$biblioteca->id]);

        // NOTA: el agrupamiento de "videos" y "audios" bajo Radio Pukllasunchis es una
        // suposición (solo "sisicha" fue confirmado explícitamente por el usuario).
        // Ajustable luego desde el CRUD de proyectos sin tocar código.
        $radio = Proyecto::firstOrCreate(
            ['slug' => 'radio-pukllasunchis'],
            [
                'nombre' => 'Radio Pukllasunchis',
                'logo' => 'logo-radio.png',
                'orden' => 2,
                'activo' => true,
            ]
        );
        $radio->roles()->syncWithoutDetaching([$sisicha->id, $videos->id, $audios->id]);

        $kawsayProyecto = Proyecto::firstOrCreate(
            ['slug' => 'kawsay'],
            [
                'nombre' => 'Kawsay',
                'logo' => 'kawsay.png',
                'orden' => 3,
                'activo' => true,
            ]
        );
        $kawsayProyecto->roles()->syncWithoutDetaching([$kawsay->id]);
    }

    public function down(): void
    {
        Proyecto::whereIn('slug', ['eesp-pukllasunchis', 'radio-pukllasunchis', 'kawsay'])->delete();
    }
};
