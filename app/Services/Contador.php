<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contador genérico de visitas / vistas / descargas (Sisipedia y Kawsay).
 *
 * - Cuenta una vez por persona (sesión) y elemento: recargar no infla el número.
 * - No cuenta robots (Google, Facebook al previsualizar un enlace, etc.).
 * - Suma con un UPDATE atómico sin tocar updated_at (las portadas ordenan por fecha).
 * - Nunca rompe la página: si la columna aún no existe (falta el .sql en
 *   producción), simplemente no cuenta.
 */
class Contador
{
    private const ROBOTS = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python|headless|lighthouse/i';

    /** @var array<string, bool> columnas ya consultadas en esta petición */
    private static array $columnas = [];

    public static function sumar(Model $modelo, string $columna, string $claveSesion, Request $request): bool
    {
        if (preg_match(self::ROBOTS, (string) $request->userAgent())) {
            return false;
        }

        $vistos = (array) $request->session()->get($claveSesion, []);
        if (in_array($modelo->getKey(), $vistos, true)) {
            return false;
        }

        try {
            DB::table($modelo->getTable())->where($modelo->getKeyName(), $modelo->getKey())->increment($columna);
            // Que la misma página ya muestre el número actualizado.
            $modelo->setAttribute($columna, (int) $modelo->getAttribute($columna) + 1);
        } catch (\Throwable $e) {
            return false; // columna aún no creada en esta base
        }

        // Tope para que la sesión no crezca sin fin si alguien recorre todo el sitio.
        $request->session()->put($claveSesion, array_slice([...$vistos, $modelo->getKey()], -300));

        return true;
    }

    /**
     * ¿Existe la columna? Para usar columnas nuevas sin romper producción mientras
     * no se ejecute el .sql. Se consulta una sola vez por petición.
     */
    public static function columnaExiste(string $tabla, string $columna): bool
    {
        $clave = "{$tabla}.{$columna}";

        return self::$columnas[$clave] ??= Schema::hasColumn($tabla, $columna);
    }

    /** "1,234": miles con coma, como se escriben en Perú (S/ 1,234.50). */
    public static function formato(int $n): string
    {
        return number_format($n);
    }
}
