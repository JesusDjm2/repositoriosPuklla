<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Visitas de registros y descargas de archivos de Sisipedia.
 *
 * - Cuenta una vez por persona (sesión) y elemento: recargar la página o tocar dos
 *   veces "Descargar" no infla el número.
 * - No cuenta robots (Google, Facebook al previsualizar un enlace, etc.).
 * - Suma con un UPDATE atómico sin tocar updated_at (la portada ordena por esa fecha).
 * - Nunca rompe la página: si la columna aún no existe en producción (falta el .sql),
 *   simplemente no cuenta.
 */
class ContadorSisipedia
{
    private const ROBOTS = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|headless|lighthouse/i';

    public static function visita(Model $registro, Request $request): void
    {
        self::sumar($registro, 'visitas', 'sisi_vistos', $request);
    }

    public static function descarga(Model $archivo, Request $request): void
    {
        self::sumar($archivo, 'descargas', 'sisi_descargados.'.$archivo->getTable(), $request);
    }

    private static function sumar(Model $modelo, string $columna, string $claveSesion, Request $request): void
    {
        if (preg_match(self::ROBOTS, (string) $request->userAgent())) {
            return;
        }

        $vistos = (array) $request->session()->get($claveSesion, []);
        if (in_array($modelo->getKey(), $vistos, true)) {
            return;
        }

        try {
            DB::table($modelo->getTable())->where($modelo->getKeyName(), $modelo->getKey())->increment($columna);
            // Que la misma página ya muestre el número actualizado.
            $modelo->setAttribute($columna, (int) $modelo->getAttribute($columna) + 1);
        } catch (\Throwable $e) {
            return; // columna aún no creada en esta base
        }

        // Tope para que la sesión no crezca sin fin si alguien recorre todo el sitio.
        $request->session()->put($claveSesion, array_slice([...$vistos, $modelo->getKey()], -300));
    }

    /** "1,234": miles con coma, como se escriben en Perú (S/ 1,234.50). */
    public static function formato(int $n): string
    {
        return number_format($n);
    }
}
