<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayVideo;
use App\Models\sisipedia\AportacionArchivo;
use App\Models\sisipedia\CategoryFile;
use App\Models\Video;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reenvía videos de Google Drive al navegador vía la Drive API (mismo patrón que
 * AudioStreamController), para poder usar un <video controls> nativo en vez del
 * iframe /preview de Drive: da play/pause/tiempo/pantalla completa reales y permite
 * cargar el video solo cuando el visitante lo pide (no en cada carga de página).
 */
class VideoStreamController extends Controller
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    /** Videos del repositorio Sisipedia (árbol de categorías). */
    public function categoryFile(Request $request, CategoryFile $categoryFile): StreamedResponse
    {
        abort_unless($categoryFile->tipo === 'video' && $categoryFile->drive_id, 404);

        return $this->streamDriveFile($request, $categoryFile->drive_id, $categoryFile->nombre_original);
    }

    /** Videos adjuntos a una aportación de un visitante. */
    public function aportacionArchivo(Request $request, AportacionArchivo $aportacionArchivo): StreamedResponse
    {
        abort_unless($aportacionArchivo->tipo === 'video' && $aportacionArchivo->drive_id, 404);

        return $this->streamDriveFile($request, $aportacionArchivo->drive_id, $aportacionArchivo->nombre_original);
    }

    /** Videos y audios subidos a un Tema de Kawsay (KawsayArchivo). Los de YouTube no pasan por aquí. */
    public function kawsayArchivo(Request $request, KawsayArchivo $archivo): StreamedResponse
    {
        abort_unless(in_array($archivo->tipo, ['video', 'audio'], true) && $archivo->drive_id, 404);

        return $this->streamDriveFile($request, $archivo->drive_id, $archivo->nombre_original, $archivo->tipo === 'audio');
    }

    /** Catálogo de Videos de Radio Pukllasunchis. */
    public function video(Request $request, Video $video): StreamedResponse
    {
        abort_unless(! empty($video->drive), 404);

        return $this->streamDriveFile($request, $video->drive, null);
    }

    /** Catálogo de Videos de Kawsay. */
    public function kawsayVideo(Request $request, KawsayVideo $kawsayVideo): StreamedResponse
    {
        abort_unless(! empty($kawsayVideo->drive), 404);

        return $this->streamDriveFile($request, $kawsayVideo->drive, null);
    }

    /**
     * Reenvía el video del archivo de Drive al navegador, respetando el header
     * Range para que el reproductor pueda adelantar/atrasar sin bajar todo el
     * archivo. Evita depender del iframe público de Drive (controles pobres, sin
     * pantalla completa fiable) y evita cargar el archivo completo en memoria de PHP.
     */
    private function streamDriveFile(Request $request, string $driveId, ?string $nombreOriginal, bool $esAudio = false): StreamedResponse
    {
        $driveResponse = $this->drive->getMediaStream($driveId, $request->header('Range'));

        $status = $driveResponse->getStatusCode();
        $body = $driveResponse->getBody();

        $headers = [
            // Drive no siempre reporta el mimetype correcto; se deduce por la
            // extensión del nombre original cuando está disponible.
            'Content-Type' => $this->mimeFromNombre($nombreOriginal, $esAudio),
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

    private function mimeFromNombre(?string $nombreOriginal, bool $esAudio = false): string
    {
        $ext = $nombreOriginal ? strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION)) : '';

        if ($esAudio) {
            return match ($ext) {
                'm4a', 'aac' => 'audio/mp4',
                'ogg', 'oga' => 'audio/ogg',
                'wav' => 'audio/wav',
                default => 'audio/mpeg',
            };
        }

        return match ($ext) {
            'webm' => 'video/webm',
            'ogg', 'ogv' => 'video/ogg',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            default => 'video/mp4',
        };
    }
}
