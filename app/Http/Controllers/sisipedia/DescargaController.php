<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\AportacionArchivo;
use App\Models\sisipedia\CategoryFile;
use App\Services\ContadorSisipedia;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;

/**
 * Descargas contadas: el botón "Descargar" pasa por aquí, se suma una descarga y se
 * redirige al enlace de descarga directa de Google Drive (el archivo no pasa por el
 * servidor, así que no consume ancho de banda del hosting).
 */
class DescargaController extends Controller
{
    public function material(Request $request, CategoryFile $categoryFile)
    {
        abort_unless($categoryFile->drive_id && $categoryFile->category?->is_active, 404);

        ContadorSisipedia::descarga($categoryFile, $request);

        return redirect()->away(GoogleDriveService::getEmbedUrl($categoryFile->drive_id));
    }

    public function aporte(Request $request, AportacionArchivo $aportacionArchivo)
    {
        // Aún en cola de subida (sin drive_id) o aporte sin aprobar: nada que descargar.
        abort_unless($aportacionArchivo->drive_id && $aportacionArchivo->aportacion?->is_approved, 404);

        ContadorSisipedia::descarga($aportacionArchivo, $request);

        return redirect()->away(GoogleDriveService::getEmbedUrl($aportacionArchivo->drive_id));
    }
}
