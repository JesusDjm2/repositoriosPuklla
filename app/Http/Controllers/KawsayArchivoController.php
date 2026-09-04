<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayTema;
use App\Services\GoogleDriveService;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;

class KawsayArchivoController extends Controller
{
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
            'titulo' => 'required|string|max:255',
            'archivo' => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($request, $tiposContenido) {
                    $info = $tiposContenido[$request->input('tipo_contenido')] ?? null;
                    $extension = strtolower($value->getClientOriginalExtension());
                    if ($info && ! in_array($extension, $info['mimes'], true)) {
                        $fail('El formato del archivo no corresponde a "' . $info['label'] . '" (permitido: ' . implode(', ', $info['mimes']) . ').');
                    }
                },
            ],
        ]);

        $info = $tiposContenido[$request->tipo_contenido];
        $maxBytes = $info['maxKb'] * 1024;
        if ($request->file('archivo')->getSize() > $maxBytes) {
            return back()->withErrors(['archivo' => 'El archivo supera el límite permitido para este tipo de contenido (' . round($maxBytes / 1024 / 1024) . 'MB).'])->withInput();
        }

        $tema = KawsayTema::findOrFail($request->kawsay_tema_id);
        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $formato = KawsayTaxonomia::formatoPorExtension($extension) ?? 'pdf';
        $nombreOriginal = $archivo->getClientOriginalName();
        $nombreDrive = $request->tipo_contenido . '-' . $tema->slug . '-' . $nombreOriginal;

        $driveId = $this->drive->upload($archivo, $formato, $nombreDrive, 'kawsay');

        KawsayArchivo::create([
            'kawsay_tema_id' => $tema->id,
            'tipo_contenido' => $request->tipo_contenido,
            'tipo' => $formato,
            'titulo' => $request->titulo,
            'drive_id' => $driveId,
            'nombre_original' => $nombreOriginal,
            'peso_mb' => round($archivo->getSize() / (1024 * 1024), 2),
        ]);

        return back()->with('success', 'Archivo subido correctamente.');
    }

    public function destroy(KawsayArchivo $archivo)
    {
        $this->drive->delete($archivo->drive_id);
        $archivo->delete();

        return back()->with('success', 'Archivo eliminado correctamente.');
    }
}
