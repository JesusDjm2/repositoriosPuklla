<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Genera backups de la base de datos en PHP puro (sin exec/Process/mysqldump),
 * porque el deploy a producción es solo por FTP en un hosting compartido donde no
 * hay garantía de acceso a shell. Recorre las tablas vía Eloquent/DB y escribe un
 * .sql equivalente a un dump manual (CREATE TABLE + INSERT).
 */
class DatabaseBackupService
{
    private string $directorio;

    public function __construct()
    {
        $this->directorio = storage_path('app/backups');
    }

    public function crear(): string
    {
        if (! is_dir($this->directorio)) {
            mkdir($this->directorio, 0755, true);
        }

        $nombre = 'kawsay-backup-'.now()->format('Y-m-d_His').'.sql';
        $ruta = "{$this->directorio}/{$nombre}";

        $handle = fopen($ruta, 'w');
        fwrite($handle, "-- Backup generado el ".now()->toDateTimeString()."\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tablas = collect(DB::select('SHOW TABLES'))
            ->map(fn ($fila) => array_values((array) $fila)[0]);

        foreach ($tablas as $tabla) {
            $this->escribirTabla($handle, $tabla);
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return $nombre;
    }

    /** @return array<int, array{nombre:string, tamano:string, fecha:\Illuminate\Support\Carbon}> */
    public function listar(): array
    {
        if (! is_dir($this->directorio)) {
            return [];
        }

        $backups = collect(glob("{$this->directorio}/*.sql"))
            ->map(function (string $ruta) {
                return [
                    'nombre' => basename($ruta),
                    'tamano' => $this->formatearTamano(filesize($ruta)),
                    'fecha' => \Illuminate\Support\Carbon::createFromTimestamp(filemtime($ruta)),
                ];
            })
            ->sortByDesc('fecha')
            ->values();

        return $backups->all();
    }

    public function eliminar(string $nombre): void
    {
        $ruta = $this->rutaSegura($nombre);

        if ($ruta && file_exists($ruta)) {
            unlink($ruta);
        }
    }

    public function rutaSegura(string $nombre): ?string
    {
        if (! preg_match('/^[A-Za-z0-9_.\-]+\.sql$/', $nombre) || str_contains($nombre, '..')) {
            return null;
        }

        return "{$this->directorio}/{$nombre}";
    }

    private function escribirTabla($handle, string $tabla): void
    {
        $create = DB::select("SHOW CREATE TABLE `{$tabla}`");
        $createSql = $create[0]->{'Create Table'} ?? null;

        if (! $createSql) {
            return;
        }

        fwrite($handle, "DROP TABLE IF EXISTS `{$tabla}`;\n");
        fwrite($handle, $createSql.";\n\n");

        DB::table($tabla)->orderBy(DB::raw('1'))->chunk(500, function ($filas) use ($handle, $tabla) {
            if ($filas->isEmpty()) {
                return;
            }

            $columnas = array_keys((array) $filas->first());
            $columnasSql = '`'.implode('`, `', $columnas).'`';

            $valores = $filas->map(function ($fila) {
                $valores = array_map(function ($valor) {
                    if (is_null($valor)) {
                        return 'NULL';
                    }

                    return "'".addslashes((string) $valor)."'";
                }, (array) $fila);

                return '('.implode(', ', $valores).')';
            })->implode(",\n");

            fwrite($handle, "INSERT INTO `{$tabla}` ({$columnasSql}) VALUES\n{$valores};\n");
        });

        fwrite($handle, "\n");
    }

    private function formatearTamano(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }

        return round($bytes / 1024, 1).' KB';
    }
}
