<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\Canciones;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\AportacionArchivo;
use App\Models\sisipedia\CategoryFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioStreamController extends Controller
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    /** Audios del repositorio Sisipedia (árbol de categorías). */
    public function categoryFile(Request $request, CategoryFile $categoryFile): StreamedResponse
    {
        abort_unless($categoryFile->tipo === 'audio' && $categoryFile->drive_id, 404);

        return $this->streamDriveFile($request, $categoryFile->drive_id);
    }

    /** Audios adjuntos a una aportación de un visitante. */
    public function aportacionArchivo(Request $request, AportacionArchivo $aportacionArchivo): StreamedResponse
    {
        abort_unless($aportacionArchivo->tipo === 'audio' && $aportacionArchivo->drive_id, 404);

        return $this->streamDriveFile($request, $aportacionArchivo->drive_id);
    }

    /** Audio del campo directo (legado) de una aportación, sin pasar por AportacionArchivo. */
    public function aportacionSingle(Request $request, Aportacion $aportacion): StreamedResponse
    {
        abort_unless(! empty($aportacion->audio), 404);

        return $this->streamDriveFile($request, $aportacion->audio);
    }

    /** Canciones del listado público (sección "Canciones"). */
    public function cancion(Request $request, Canciones $cancion): StreamedResponse
    {
        abort_unless(! empty($cancion->drive), 404);

        return $this->streamDriveFile($request, $cancion->drive);
    }

    /**
     * Reenvía el audio del archivo de Drive al navegador, respetando el header
     * Range para que el reproductor pueda adelantar/atrasar sin bajar todo el
     * archivo. Evita depender del iframe público de Drive (poco fiable con
     * tráfico real) y evita cargar el archivo completo en memoria de PHP.
     */
    private function streamDriveFile(Request $request, string $driveId): StreamedResponse
    {
        $driveResponse = $this->drive->getMediaStream($driveId, $request->header('Range'));

        $status = $driveResponse->getStatusCode();
        $body = $driveResponse->getBody();

        $headers = [
            // Drive a veces reporta un mimetype incorrecto para el mismo archivo (ej.
            // "video/mpeg" para un mp3); estas rutas solo sirven audio, así que se fuerza.
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
        ];
        if ($driveResponse->hasHeader('Content-Length')) {
            $headers['Content-Length'] = $driveResponse->getHeaderLine('Content-Length');
        }
        if ($driveResponse->hasHeader('Content-Range')) {
            $headers['Content-Range'] = $driveResponse->getHeaderLine('Content-Range');
        }

        return response()->stream(function () use ($body) {
            while (! $body->eof()) {
                echo $body->read(8192);
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, $status, $headers);
    }
}
