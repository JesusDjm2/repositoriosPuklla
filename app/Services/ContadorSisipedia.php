<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Visitas de registros y descargas de archivos de Sisipedia.
 * La lógica (una vez por sesión, sin robots, sin romper si falta la columna)
 * vive en App\Services\Contador, compartida con Kawsay.
 */
class ContadorSisipedia
{
    public static function visita(Model $registro, Request $request): void
    {
        Contador::sumar($registro, 'visitas', 'sisi_vistos', $request);
    }

    public static function descarga(Model $archivo, Request $request): void
    {
        Contador::sumar($archivo, 'descargas', 'sisi_descargados.'.$archivo->getTable(), $request);
    }

    /** "1,234": miles con coma, como se escriben en Perú (S/ 1,234.50). */
    public static function formato(int $n): string
    {
        return Contador::formato($n);
    }
}
