<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayTema;
use App\Services\Contador;
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
            'descripcion' => 'nullable|string|max:500',
            'nivel' => 'nullable|string|max:100',
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
            ] + $this->datosDescriptivos($request));
            $subidos++;
        }

        if ($fallidos) {
            return back()->with('error', "Se subieron {$subidos} de " . count($archivos) . '. No se pudo subir: ' . implode(', ', $fallidos) . '.');
        }

        return back()->with('success', $subidos === 1 ? 'Archivo subido correctamente.' : "{$subidos} archivos subidos correctamente.");
    }

    public function destroy(KawsayArchivo $archivo)
    {
        if ($archivo->drive_id) {
            $this->drive->delete($archivo->drive_id);
        }
        $this->borrarPortada($archivo);
        $archivo->delete();

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    /** Editar título, "para qué sirve" y "grado o nivel" de un archivo ya subido. */
    public function update(Request $request, KawsayArchivo $archivo)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'nivel' => 'nullable|string|max:100',
        ], ['titulo.required' => 'El archivo necesita un título.']);

        $archivo->update(['titulo' => trim($request->titulo)] + $this->datosDescriptivos($request));

        return back()->with('success', 'Datos del archivo actualizados.');
    }

    /**
     * Video por enlace de YouTube: YouTube adapta la calidad a la señal de cada
     * persona (clave con datos móviles). Se guarda como tipo "video" con youtube_id
     * y drive_id vacío, así cuenta y se agrupa igual que los demás videos.
     */
    public function youtube(Request $request)
    {
        if (! Contador::columnaExiste('kawsay_archivos', 'youtube_id')) {
            return back()->with('error', 'Falta ejecutar el SQL de Kawsay (20261111) en la base de datos para poder agregar videos de YouTube.');
        }

        $request->validate([
            'kawsay_tema_id' => 'required|exists:kawsay_temas,id',
            'enlace' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'nivel' => 'nullable|string|max:100',
        ], [
            'enlace.required' => 'Pega el enlace del video de YouTube.',
            'titulo.required' => 'Escribe un título para el video.',
        ]);

        $id = KawsayArchivo::youtubeIdDe($request->enlace);
        if (! $id) {
            return back()->withErrors(['enlace' => 'Ese enlace no parece de YouTube. Copia el enlace del video (por ejemplo https://youtu.be/…).'])->withInput();
        }

        KawsayArchivo::create([
            'kawsay_tema_id' => $request->kawsay_tema_id,
            'tipo_contenido' => 'video',
            'tipo' => 'video',
            'titulo' => trim($request->titulo),
            'drive_id' => '',
            'youtube_id' => $id,
            'nombre_original' => 'https://youtu.be/' . $id,
        ] + $this->datosDescriptivos($request));

        return back()->with('success', 'Video de YouTube agregado.');
    }

    /** descripcion / nivel solo si las columnas ya existen (en producción, tras el .sql). */
    private function datosDescriptivos(Request $request): array
    {
        if (! Contador::columnaExiste('kawsay_archivos', 'descripcion')) {
            return [];
        }

        return [
            'descripcion' => trim((string) $request->input('descripcion')) ?: null,
            'nivel' => trim((string) $request->input('nivel')) ?: null,
        ];
    }

    /**
     * Portada propia de un video: Drive usa como miniatura el primer fotograma,
     * que casi siempre sale negro. Se guarda en public/img/kawsay/portadas.
     */
    public function portada(Request $request, KawsayArchivo $archivo)
    {
        if (! Contador::columnaExiste('kawsay_archivos', 'portada')) {
            return back()->with('error', 'Falta ejecutar el SQL de Kawsay en la base de datos para poder guardar portadas.');
        }

        $request->validate([
            'portada' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'portada.required' => 'Elige una imagen para la portada.',
            'portada.image' => 'La portada debe ser una imagen (JPG, PNG o WebP).',
            'portada.max' => 'La portada no puede pesar más de 4 MB.',
        ]);

        $file = $request->file('portada');
        $directorio = public_path('img/kawsay/portadas');
        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        $nombre = 'video-' . $archivo->id . '-' . Str::random(6) . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($directorio, $nombre);

        $this->borrarPortada($archivo);
        $archivo->update(['portada' => 'img/kawsay/portadas/' . $nombre]);

        return back()->with('success', 'Portada actualizada.');
    }

    public function quitarPortada(KawsayArchivo $archivo)
    {
        $this->borrarPortada($archivo);
        $archivo->update(['portada' => null]);

        return back()->with('success', 'Portada quitada: se usará la miniatura de Drive.');
    }

    /** Suma una vista (una vez por sesión). Lo llaman la portada y el tema al abrir el archivo. */
    public function vista(Request $request, KawsayArchivo $archivo)
    {
        Contador::sumar($archivo, 'vistas', 'kawsay_vistos', $request);

        return response()->noContent();
    }

    private function borrarPortada(KawsayArchivo $archivo): void
    {
        $ruta = $archivo->portada ?? null;
        // Solo archivos de nuestra carpeta de portadas (nunca rutas arbitrarias).
        if ($ruta && str_starts_with($ruta, 'img/kawsay/portadas/') && is_file(public_path($ruta))) {
            @unlink(public_path($ruta));
        }
    }
}
