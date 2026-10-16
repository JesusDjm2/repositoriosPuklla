<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayTema;
use App\Services\GoogleDriveService;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KawsayArchivoController extends Controller
{
    private const MAX_ARCHIVOS_POR_ENVIO = 20;

    public function __construct(private GoogleDriveService $drive)
    {
    }

    public function index(Request $request)
    {
        $areas = KawsayArea::orderBy('orden')->get();
        $tiposContenido = KawsayTaxonomia::tiposContenido();

        $query = KawsayArchivo::with('tema.area')->latest();

        if ($request->filled('area')) {
            $query->whereHas('tema', fn ($q) => $q->where('kawsay_area_id', $request->area));
        }
        if ($request->filled('kawsay_tema_id')) {
            $query->where('kawsay_tema_id', $request->kawsay_tema_id);
        }
        if ($request->filled('tipo_contenido')) {
            $query->where('tipo_contenido', $request->tipo_contenido);
        }

        $archivos = $query->paginate(20)->withQueryString();

        return view('kawsay.archivos.index', compact('areas', 'tiposContenido', 'archivos'));
    }

    public function store(Request $request)
    {
        $tiposContenido = KawsayTaxonomia::tiposContenido();

        $request->validate([
            'kawsay_tema_id' => 'required|exists:kawsay_temas,id',
            'tipo_contenido' => 'required|in:' . implode(',', array_keys($tiposContenido)),
            'titulo' => 'nullable|string|max:255',
            'archivo' => 'required|array|min:1|max:' . self::MAX_ARCHIVOS_POR_ENVIO,
            'archivo.*' => [
                'file',
                function ($attribute, $value, $fail) use ($request, $tiposContenido) {
                    $info = $tiposContenido[$request->input('tipo_contenido')] ?? null;
                    $extension = strtolower($value->getClientOriginalExtension());
                    if ($info && ! in_array($extension, $info['mimes'], true)) {
                        $fail('"' . $value->getClientOriginalName() . '": el formato no corresponde a "' . $info['label'] . '" (permitido: ' . implode(', ', $info['mimes']) . ').');
                    }
                },
            ],
        ], [
            'archivo.required' => 'Elige al menos un archivo.',
            'archivo.max' => 'Puedes subir hasta ' . self::MAX_ARCHIVOS_POR_ENVIO . ' archivos a la vez.',
            'archivo.*.file' => 'Uno de los archivos no se pudo leer (¿pesa más de lo que permite el servidor?).',
        ]);

        $info = $tiposContenido[$request->tipo_contenido];
        $maxBytes = $info['maxKb'] * 1024;
        foreach ($request->file('archivo') as $archivo) {
            if ($archivo->getSize() > $maxBytes) {
                return back()->withErrors(['archivo' => '"' . $archivo->getClientOriginalName() . '" supera el límite para este tipo de contenido (' . round($maxBytes / 1024 / 1024) . ' MB).'])->withInput();
            }
        }

        // Cada archivo es una subida a Drive; con varios, el tiempo total puede pasar el límite de PHP.
        @set_time_limit(0);

        $tema = KawsayTema::findOrFail($request->kawsay_tema_id);
        $archivos = array_values($request->file('archivo'));
        $titulo = trim((string) $request->input('titulo'));
        $subidos = 0;
        $fallidos = [];

        foreach ($archivos as $i => $archivo) {
            $extension = strtolower($archivo->getClientOriginalExtension());
            $formato = KawsayTaxonomia::formatoPorExtension($extension) ?? 'pdf';
            $nombreOriginal = $archivo->getClientOriginalName();
            $nombreDrive = $request->tipo_contenido . '-' . $tema->slug . '-' . $nombreOriginal;

            // Sin título → el nombre del archivo; con título y varios archivos → "Título 1", "Título 2"…
            $tituloArchivo = match (true) {
                $titulo === '' => Str::limit(pathinfo($nombreOriginal, PATHINFO_FILENAME), 250, ''),
                count($archivos) > 1 => Str::limit($titulo, 240, '') . ' ' . ($i + 1),
                default => $titulo,
            };

            try {
                $driveId = $this->drive->upload($archivo, $formato, $nombreDrive, 'kawsay');
            } catch (\Throwable $e) {
                report($e);
                $fallidos[] = $nombreOriginal;
                continue;
            }

            KawsayArchivo::create([
                'kawsay_tema_id' => $tema->id,
                'tipo_contenido' => $request->tipo_contenido,
                'tipo' => $formato,
                'titulo' => $tituloArchivo,
                'drive_id' => $driveId,
                'nombre_original' => $nombreOriginal,
                'peso_mb' => round($archivo->getSize() / (1024 * 1024), 2),
            ]);
            $subidos++;
        }

        if ($fallidos) {
            return back()->with('error', "Se subieron {$subidos} de " . count($archivos) . '. No se pudo subir: ' . implode(', ', $fallidos) . '.');
        }

        return back()->with('success', $subidos === 1 ? 'Archivo subido correctamente.' : "{$subidos} archivos subidos correctamente.");
    }

    public function destroy(KawsayArchivo $archivo)
    {
        $this->drive->delete($archivo->drive_id);
        $archivo->delete();

        return back()->with('success', 'Archivo eliminado correctamente.');
    }
}
