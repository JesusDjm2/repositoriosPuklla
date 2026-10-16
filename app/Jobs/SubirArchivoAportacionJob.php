<?php

namespace App\Jobs;

use App\Models\sisipedia\AportacionArchivo;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Sube a Google Drive, en segundo plano, un archivo que el visitante ya adjuntó
 * a una aportación. Antes esto se hacía dentro de la misma petición HTTP del
 * formulario: con internet lento, re-subir un video grande a Drive sumaba tiempo
 * suficiente para que el proxy del hosting cortara la conexión con un 504 Gateway
 * Timeout, aunque PHP todavía tuviera margen (max_execution_time=300). Ahora el
 * formulario solo guarda el archivo en disco local (rápido) y responde al
 * instante; este job hace el resto.
 */
class SubirArchivoAportacionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 290;

    public function __construct(public int $aportacionArchivoId)
    {
    }

    /** @return array<int,int> */
    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(GoogleDriveService $drive): void
    {
        $archivo = AportacionArchivo::find($this->aportacionArchivoId);

        if (! $archivo || ! $archivo->ruta_local) {
            return;
        }

        $disco = Storage::disk('local');

        if (! $disco->exists($archivo->ruta_local)) {
            $archivo->update(['fallo_en' => now()]);

            return;
        }

        $uploadedFile = new UploadedFile(
            $disco->path($archivo->ruta_local),
            $archivo->nombre_original ?? basename($archivo->ruta_local),
            null,
            null,
            true
        );

        $driveId = $drive->upload($uploadedFile, $archivo->tipo, basename($archivo->ruta_local));

        $rutaLocal = $archivo->ruta_local;
        $archivo->update(['drive_id' => $driveId, 'ruta_local' => null]);

        $disco->delete($rutaLocal);
    }

    public function failed(\Throwable $e): void
    {
        AportacionArchivo::find($this->aportacionArchivoId)?->update(['fallo_en' => now()]);

        report($e);
    }
}
