<?php

namespace App\Services;

use App\Models\KawsayTemaComentario;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Defensa propia contra spam/bots en los comentarios públicos (sin reCAPTCHA
 * ni servicios externos). Capas, de más barata a más específica:
 *
 *  1. Honeypot: un campo oculto que una persona nunca llena; un bot que
 *     rellena todo lo que ve, sí. Se responde "éxito" falso para no darle
 *     pistas, y no se guarda nada.
 *  2. Token de tiempo firmado: se emite al renderizar el formulario. Un bot
 *     que envía al instante (o reutiliza un token viejo/inventado) es
 *     rechazado; no se puede falsificar porque va cifrado con la APP_KEY.
 *  3. Límite de frecuencia por IP: pocas publicaciones por ventana corta y
 *     por día, y un tope más holgado de intentos (aunque se rechacen).
 *  4. Contenido: sin enlaces/correos (el spam casi siempre los lleva), sin
 *     letras repetidas en masa, y sin duplicados del mismo texto en el mismo
 *     tema durante 24 h.
 */
class AntiSpamComentarios
{
    private const MIN_SEGUNDOS = 4;
    private const MAX_SEGUNDOS = 7200;
    private const LIMITE_VENTANA = 3;          // comentarios PUBLICADOS…
    private const SEGUNDOS_VENTANA = 600;      // …cada 10 min por IP
    private const LIMITE_DIA = 10;             // comentarios publicados por IP al día
    private const LIMITE_INTENTOS = 12;        // envíos (aunque se rechacen) por IP cada 10 min: frena el "tanteo"
    private const CAMPO_HONEYPOT = 'sitio_web';

    public static function token(): string
    {
        return Crypt::encryptString((string) time());
    }

    /**
     * @return array{ok: bool, bot?: bool, error?: string}
     */
    public function evaluar(Request $request, int $temaId, string $nombre, string $comentario): array
    {
        // 1) Honeypot
        if (filled($request->input(self::CAMPO_HONEYPOT))) {
            return ['ok' => false, 'bot' => true];
        }

        // 2) Token de tiempo
        $error = $this->validarToken((string) $request->input('_t'));
        if ($error) {
            return ['ok' => false, 'error' => $error];
        }

        // 3) Frecuencia por IP
        $ip = $request->ip() ?: 'desconocida';

        if (RateLimiter::tooManyAttempts('kawsay-com:intentos:'.$ip, self::LIMITE_INTENTOS)) {
            return ['ok' => false, 'error' => 'Demasiados intentos seguidos. Espera unos minutos e inténtalo otra vez.'];
        }
        RateLimiter::hit('kawsay-com:intentos:'.$ip, self::SEGUNDOS_VENTANA);

        if (RateLimiter::tooManyAttempts('kawsay-com:ventana:'.$ip, self::LIMITE_VENTANA)
            || RateLimiter::tooManyAttempts('kawsay-com:dia:'.$ip, self::LIMITE_DIA)) {
            $espera = max(RateLimiter::availableIn('kawsay-com:ventana:'.$ip), 1);

            return ['ok' => false, 'error' => 'Publicaste varios comentarios seguidos. Intenta de nuevo en '.ceil($espera / 60).' min.'];
        }

        // 4) Contenido
        if ($this->contieneEnlaces($nombre) || $this->contieneEnlaces($comentario)) {
            return ['ok' => false, 'error' => 'Por seguridad no se permiten enlaces ni correos en los comentarios.'];
        }

        if (preg_match('/(.)\1{9,}/u', $comentario) || preg_match('/(.)\1{9,}/u', $nombre)) {
            return ['ok' => false, 'error' => 'El comentario tiene demasiados caracteres repetidos.'];
        }

        $duplicado = KawsayTemaComentario::where('kawsay_tema_id', $temaId)
            ->where('created_at', '>=', now()->subDay())
            ->whereRaw('LOWER(comentario) = ?', [mb_strtolower($comentario)])
            ->exists();
        if ($duplicado) {
            return ['ok' => false, 'error' => 'Ese comentario ya fue publicado en este tema.'];
        }

        return ['ok' => true];
    }

    /** Cuenta un comentario ya publicado contra los límites por IP. */
    public function registrarPublicacion(Request $request): void
    {
        $ip = $request->ip() ?: 'desconocida';
        RateLimiter::hit('kawsay-com:ventana:'.$ip, self::SEGUNDOS_VENTANA);
        RateLimiter::hit('kawsay-com:dia:'.$ip, 86400);
    }

    /** Quita etiquetas HTML, controles y espacios de sobra. */
    public static function limpiar(string $texto, bool $multilinea = false): string
    {
        $texto = strip_tags($texto);
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texto) ?? '';

        if ($multilinea) {
            $texto = preg_replace("/\r\n?/", "\n", $texto) ?? '';
            $texto = preg_replace("/[ \t]+/", ' ', $texto) ?? '';
            $texto = preg_replace("/\n{3,}/", "\n\n", $texto) ?? '';
        } else {
            $texto = preg_replace('/\s+/u', ' ', $texto) ?? '';
        }

        return trim($texto);
    }

    private function validarToken(string $token): ?string
    {
        try {
            $emitido = (int) Crypt::decryptString($token);
        } catch (DecryptException) {
            return 'El formulario caducó. Recarga la página e inténtalo otra vez.';
        }

        $edad = time() - $emitido;

        if ($edad < self::MIN_SEGUNDOS) {
            return 'Enviaste el comentario demasiado rápido. Espera un momento e inténtalo otra vez.';
        }

        if ($edad > self::MAX_SEGUNDOS) {
            return 'El formulario caducó. Recarga la página e inténtalo otra vez.';
        }

        return null;
    }

    private function contieneEnlaces(string $texto): bool
    {
        return (bool) preg_match(
            '~(https?:|ftp:|www\.|@[a-z0-9.-]+\.[a-z]{2,}|[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}|\b[a-z0-9-]+\.(com|net|org|info|biz|xyz|ru|cn|top|site|online|club|shop|link|click|pe)\b/?)~iu',
            $texto
        );
    }
}
