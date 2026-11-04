<?php

namespace App\Http\Controllers;

use App\Models\Canciones;
use App\Models\Categoria;
use App\Models\CategoriaFisica;
use App\Models\KawsayArea;
use App\Models\KawsayCategoria;
use App\Models\KawsayLibro;
use App\Models\KawsayVideo;
use App\Models\KawsayVideoCategoria;
use App\Models\Libro;
use App\Models\LibroFisico;
use App\Models\Proyecto;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\AportacionArchivo;
use App\Models\sisipedia\Category;
use App\Models\sisipedia\CategoryFile;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EnlacesController extends Controller
{
    /**
     * Umbral mínimo de similitud (similar_text de PHP, 0–100).
     * Ej.: "linderaj" vs "lindejare" ≈ 70,6 % — con 70 % sí aparece el registro.
     * Si subes a 90 %, parejas con pequeñas diferencias dejarían de coincidir.
     */
    private const FUZZY_SIMILARITY_THRESHOLD = 70.0;

    /** Patrón LIKE seguro e insensible a mayúsculas (SQL). */
    private static function sqlLikePattern(string $query): string
    {
        $t = mb_strtolower(trim($query));

        return '%'.addcslashes($t, '%_\\').'%';
    }

    public static function needleNorm(string $query): string
    {
        return mb_strtolower(trim($query));
    }

    /**
     * Mejor similitud entre la consulta y un texto largo:
     * coincidencia parcial (substring) = 100 %; si no, similar_text global y por palabras.
     */
    public static function fuzzyBestPercent(string $needleNorm, ?string $haystack): float
    {
        if ($haystack === null || $haystack === '') {
            return 0.0;
        }
        $hay = mb_strtolower(trim($haystack));
        $n = mb_strlen($needleNorm);
        if ($n < 1) {
            return 0.0;
        }
        if ($hay === '') {
            return 0.0;
        }
        if (str_contains($hay, $needleNorm)) {
            return 100.0;
        }

        similar_text($needleNorm, $hay, $pct);
        $best = (float) $pct;

        foreach (preg_split('/\s+/u', $hay) as $word) {
            if (mb_strlen($word) < 2) {
                continue;
            }
            similar_text($needleNorm, $word, $wp);
            if ((float) $wp > $best) {
                $best = (float) $wp;
            }
        }

        return $best;
    }

    /** ¿La consulta coincide por texto contenido o por similitud ≥ umbral? */
    public static function fieldMatches(string $needleNorm, ?string $field): bool
    {
        return self::fuzzyBestPercent($needleNorm, $field) >= self::FUZZY_SIMILARITY_THRESHOLD;
    }

    /** Portada del repositorio central: raíz con los proyectos de la Asociación. */
    public function hub()
    {
        $activos = Proyecto::activos()->ordenados()->get()->map(fn (Proyecto $p) => [
            'nombre' => $p->nombre,
            'logo' => $p->logo,
            'url' => $p->urlPublica(),
            'descripcion' => $p->descripcion,
            'color' => $p->color,
        ]);

        // Los proyectos "próximamente" (sin ruta pública todavía) van siempre al
        // final, sin importar su campo `orden` — por ser los últimos en crearse.
        $conUrl = $activos->filter(fn (array $p) => $p['url'])->values();
        $sinUrl = $activos->filter(fn (array $p) => ! $p['url'])->values();
        $activos = $conUrl->concat($sinUrl)->values();

        $vacios = collect(range(1, max(0, 6 - $activos->count())))
            ->map(fn () => ['nombre' => null, 'logo' => null, 'url' => null]);

        $proyectos = $activos->concat($vacios)->values();

        return view('inicio', compact('proyectos'));
    }

    /** Portada pública de la Biblioteca EESP Pukllasunchis. */
    public function eesp()
    {
        $libros = Libro::latest()->take(8)->get();
        $videos = Video::latest()->take(8)->get();
        $librosFisicos = LibroFisico::latest()->with('categoriasFisicas')->take(16)->get();

        $categoriasVirtuales = Categoria::withCount('libros')
            ->has('libros')
            ->orderBy('nombre')
            ->get();
        $categoriasFisicas = CategoriaFisica::withCount('librosFisicos')
            ->has('librosFisicos')
            ->orderBy('nombre')
            ->get();
        $categoriasVideos = VideoCategoria::withCount('videos')
            ->has('videos')
            ->orderBy('nombre')
            ->get();

        return view('eesppukllasunchis', compact(
            'libros',
            'videos',
            'librosFisicos',
            'categoriasVirtuales',
            'categoriasFisicas',
            'categoriasVideos'
        ));
    }

    /** Portada pública del repositorio Kawsay. */
    public function kawsay()
    {
        $libros = KawsayLibro::latest()->take(8)->get();
        $videos = KawsayVideo::latest()->take(8)->get();

        $categoriasVirtuales = KawsayCategoria::withCount('libros')
            ->has('libros')
            ->orderBy('nombre')
            ->get();
        $categoriasVideos = KawsayVideoCategoria::withCount('videos')
            ->has('videos')
            ->orderBy('nombre')
            ->get();

        $areas = KawsayArea::orderBy('orden')->get();

        return view('kawsay', compact('libros', 'videos', 'categoriasVirtuales', 'categoriasVideos', 'areas'));
    }

    public function index()
    {
        $ultimosAudios = $this->latestArchivosPorTipo('audio');
        $ultimosPdf = $this->latestArchivosPorTipo('pdf');
        $ultimosImagenes = $this->latestArchivosPorTipo('imagen');
        $ultimosVideos = $this->latestArchivosPorTipo('video');

        $ultimosAportes = Aportacion::query()
            ->where('is_approved', true)
            ->whereNotNull('category_id')
            ->with(['category.parent.parent'])
            ->latest()
            ->take(8)
            ->get();

        return view('welcome', compact(
            'ultimosAudios',
            'ultimosPdf',
            'ultimosImagenes',
            'ultimosVideos',
            'ultimosAportes'
        ));
    }

    /**
     * Últimos N archivos de un tipo (audio/pdf/imagen/video) en todo el árbol Sisipedia,
     * combinando archivos curados por categoría y archivos de aportaciones aprobadas.
     */
    private function latestArchivosPorTipo(string $tipo, int $limit = 4): Collection
    {
        $deCategoria = CategoryFile::query()
            ->where('tipo', $tipo)
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->with(['category.parent.parent'])
            ->latest('updated_at')
            ->take($limit)
            ->get()
            ->map(fn (CategoryFile $f) => (object) [
                'tipo' => $f->tipo,
                'drive_id' => $f->drive_id,
                'nombre' => $f->nombre_original,
                'fecha' => $f->updated_at,
                'category' => $f->category,
                'aportante' => null,
                'stream_url' => match ($f->tipo) {
                    'audio' => route('sisipedia.audio.stream', $f),
                    'video' => route('sisipedia.video.stream', $f),
                    default => null,
                },
            ]);

        $deAportaciones = AportacionArchivo::query()
            ->where('tipo', $tipo)
            // Recién subidos siguen en cola (SubirArchivoAportacionJob) o fallaron:
            // aún no tienen drive_id y no hay nada que previsualizar.
            ->whereNotNull('drive_id')
            ->whereHas('aportacion', fn ($q) => $q->where('is_approved', true))
            ->with(['aportacion.category.parent.parent'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn (AportacionArchivo $f) => (object) [
                'tipo' => $f->tipo,
                'drive_id' => $f->drive_id,
                'nombre' => $f->nombre_original,
                'fecha' => $f->created_at,
                'category' => $f->aportacion?->category,
                'aportante' => $f->aportacion?->nombre_ol,
                'stream_url' => match ($f->tipo) {
                    'audio' => route('sisipedia.audio.aportacion.stream', $f),
                    'video' => route('sisipedia.video.aportacion.stream', $f),
                    default => null,
                },
            ]);

        return $deCategoria->concat($deAportaciones)
            ->sortByDesc(fn ($item) => $item->fecha)
            ->take($limit)
            ->values();
    }

    public function busqueda(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        if (mb_strlen($query) < 3) {
            return redirect()->route('index')->with(
                'busqueda_error',
                'Escribe al menos 3 caracteres para buscar.'
            );
        }

        $like = self::sqlLikePattern($query);
        $needle = self::needleNorm($query);

        $canciones = Canciones::cursor()->filter(function ($c) use ($needle) {
            return self::fieldMatches($needle, $c->nombre)
                || self::fieldMatches($needle, $c->autor);
        })->collect();

        $cancionesCount = $canciones->count();

        $videos = Video::cursor()->filter(function ($v) use ($needle) {
            return self::fieldMatches($needle, $v->nombre)
                || self::fieldMatches($needle, $v->descripcion ?? '');
        })->collect();

        $videosScored = self::scoreVideosFromCollection($videos, $needle);
        $videosCount = $videos->count();

        $libros = Libro::cursor()->filter(function ($l) use ($needle) {
            return self::fieldMatches($needle, $l->nombre)
                || self::fieldMatches($needle, $l->autor);
        })->collect();

        $librosCount = $libros->count();

        $kawsayLibros = KawsayLibro::cursor()->filter(function ($l) use ($needle) {
            return self::fieldMatches($needle, $l->nombre)
                || self::fieldMatches($needle, $l->autor);
        })->collect();

        $kawsayLibrosCount = $kawsayLibros->count();

        $kawsayVideos = KawsayVideo::cursor()->filter(function ($v) use ($needle) {
            return self::fieldMatches($needle, $v->nombre)
                || self::fieldMatches($needle, $v->descripcion ?? '');
        })->collect();

        $kawsayVideosScored = self::scoreVideosFromCollection($kawsayVideos, $needle);
        $kawsayVideosCount = $kawsayVideos->count();

        $sisipediaHits = $this->buildSisipediaHits($needle);
        $sisipediaCount = $sisipediaHits->count();

        $aportaciones = Aportacion::with('category.parent.parent')
            ->where('is_approved', true)
            ->cursor()
            ->filter(function ($a) use ($needle) {
                return self::fieldMatches($needle, $a->nombre_ol)
                    || self::fieldMatches($needle, $a->institucion)
                    || self::fieldMatches($needle, $a->ubicacion)
                    || self::fieldMatches($needle, $a->detalle);
            })
            ->take(80)
            ->collect();

        $aportacionesCount = $aportaciones->count();

        $hasResults = ! $canciones->isEmpty()
            || ! $videos->isEmpty()
            || ! $libros->isEmpty()
            || ! $kawsayLibros->isEmpty()
            || ! $kawsayVideos->isEmpty()
            || $sisipediaCount > 0
            || ! $aportaciones->isEmpty();

        // Nombre del proyecto a mostrar junto a cada grupo de resultados (una sola
        // fuente de verdad: la tabla `proyectos`, no strings repetidos en la vista).
        $proyectosPorSlug = Proyecto::pluck('nombre', 'slug');

        // A dónde lleva "explorar" cada grupo de resultados dentro de su propio
        // proyecto (badge clicable en cada encabezado de sección).
        $enlacesGrupos = [
            'sisipedia' => route('public.sisi'),
            'videos' => route('videos'),
            'libros' => route('libros'),
            'kawsayLibros' => route('kawsaylibros.lista'),
            'kawsayVideos' => route('kawsayvideos.lista'),
            'aportaciones' => route('public.sisi'),
            'canciones' => route('cancionesPuklla'),
        ];

        return view('buscador', [
            'canciones' => $canciones,
            'videos' => $videos,
            'videosScored' => $videosScored,
            'libros' => $libros,
            'kawsayLibros' => $kawsayLibros,
            'kawsayVideos' => $kawsayVideos,
            'kawsayVideosScored' => $kawsayVideosScored,
            'sisipediaHits' => $sisipediaHits,
            'aportaciones' => $aportaciones,
            'query' => $query,
            'hasResults' => $hasResults,
            'cancionesCount' => $cancionesCount,
            'videosCount' => $videosCount,
            'librosCount' => $librosCount,
            'kawsayLibrosCount' => $kawsayLibrosCount,
            'kawsayVideosCount' => $kawsayVideosCount,
            'sisipediaCount' => $sisipediaCount,
            'aportacionesCount' => $aportacionesCount,
            'proyectosPorSlug' => $proyectosPorSlug,
            'enlacesGrupos' => $enlacesGrupos,
            'fuzzyThreshold' => self::FUZZY_SIMILARITY_THRESHOLD,
        ]);
    }

    /**
     * Reutilizable para cualquier modelo de video con campos nombre/descripcion
     * (Video de Radio Pukllasunchis o KawsayVideo).
     *
     * @return Collection<int, array{model: object, similarity: float, match_direct: bool}>
     */
    private static function scoreVideosFromCollection(Collection $videos, string $needle): Collection
    {
        return $videos->map(function ($v) use ($needle) {
            $simNombre = self::fuzzyBestPercent($needle, $v->nombre ?? '');
            $simDesc = self::fuzzyBestPercent($needle, $v->descripcion ?? '');
            $best = max($simNombre, $simDesc);
            $direct = str_contains(mb_strtolower((string) ($v->nombre ?? '')), $needle)
                || str_contains(mb_strtolower((string) ($v->descripcion ?? '')), $needle);

            return [
                'model' => $v,
                'similarity' => round($best, 1),
                'match_direct' => $direct,
            ];
        })->sortByDesc(fn (array $r) => $r['similarity'])->values();
    }

    /**
     * Registros Sisipedia: substring + similitud difusa en título, descripción y nombres de archivo.
     *
     * @return Collection<int, array{category: Category, similarity: float, match_direct: bool, sources: array<int, array<string, mixed>>}>
     */
    private function buildSisipediaHits(string $needle): Collection
    {
        $hits = [];

        $tipoColors = ['pdf' => 'danger', 'doc' => 'primary', 'audio' => 'success', 'video' => 'warning'];

        $categories = Category::where('is_active', true)
            ->with(['parent.parent.parent', 'files'])
            ->get();

        foreach ($categories as $cat) {
            $sources = [];
            $scores = [];
            $matchedBySubstring = false;

            $nameLower = mb_strtolower($cat->name);
            $nameDirect = str_contains($nameLower, $needle);
            $simName = self::fuzzyBestPercent($needle, $cat->name);

            if ($nameDirect) {
                $matchedBySubstring = true;
                $scores[] = 100.0;
                $sources[] = [
                    'kind' => 'direct',
                    'label' => 'Texto encontrado en el título',
                ];
            } elseif ($simName >= self::FUZZY_SIMILARITY_THRESHOLD) {
                $scores[] = $simName;
                $sources[] = [
                    'kind' => 'fuzzy',
                    'label' => 'Título · similitud '.round($simName, 1).' %',
                    'similarity' => round($simName, 1),
                ];
            }

            if ($cat->description) {
                $descLower = mb_strtolower($cat->description);
                $descDirect = str_contains($descLower, $needle);
                $simDesc = self::fuzzyBestPercent($needle, $cat->description);

                if ($descDirect) {
                    $matchedBySubstring = true;
                    $scores[] = 100.0;
                    $sources[] = [
                        'kind' => 'direct',
                        'label' => 'Texto encontrado en la descripción',
                    ];
                } elseif ($simDesc >= self::FUZZY_SIMILARITY_THRESHOLD) {
                    $scores[] = $simDesc;
                    $sources[] = [
                        'kind' => 'fuzzy',
                        'label' => 'Descripción · similitud '.round($simDesc, 1).' %',
                        'similarity' => round($simDesc, 1),
                    ];
                }
            }

            foreach ($cat->files as $file) {
                $labelText = $file->nombre_original ?: $file->nombre_display;
                $hay = $file->nombre_original ?? $file->nombre_display;
                $simFile = self::fuzzyBestPercent($needle, $hay);

                $tipoMatch = str_contains(mb_strtolower($file->tipo), $needle)
                    || self::fuzzyBestPercent($needle, $file->tipo) >= self::FUZZY_SIMILARITY_THRESHOLD;

                $fileDirect = ($file->nombre_original && str_contains(mb_strtolower($file->nombre_original), $needle));

                if ($fileDirect) {
                    $matchedBySubstring = true;
                    $scores[] = 100.0;
                    $sources[] = [
                        'kind' => 'archivo_direct',
                        'filename' => $file->nombre_display,
                        'tipo' => $file->tipo,
                        'badge' => $tipoColors[$file->tipo] ?? 'secondary',
                        'label' => 'Nombre de archivo',
                    ];
                } elseif ($simFile >= self::FUZZY_SIMILARITY_THRESHOLD) {
                    $scores[] = $simFile;
                    $sources[] = [
                        'kind' => 'archivo_fuzzy',
                        'filename' => $file->nombre_display,
                        'tipo' => $file->tipo,
                        'badge' => $tipoColors[$file->tipo] ?? 'secondary',
                        'label' => 'Archivo · ~'.round($simFile, 1).' % · '.$labelText,
                        'similarity' => round($simFile, 1),
                    ];
                } elseif ($tipoMatch && ! $file->nombre_original) {
                    $scores[] = max(60.0, self::fuzzyBestPercent($needle, $file->tipo));
                    $sources[] = [
                        'kind' => 'archivo_tipo',
                        'filename' => $file->nombre_display,
                        'tipo' => $file->tipo,
                        'badge' => $tipoColors[$file->tipo] ?? 'secondary',
                        'label' => 'Tipo de archivo',
                    ];
                }
            }

            if ($scores === []) {
                continue;
            }

            $best = max($scores);

            $hits[$cat->id] = [
                'category' => $cat,
                'similarity' => round($best, 1),
                'match_direct' => $matchedBySubstring,
                'sources' => $sources,
            ];
        }

        return collect($hits)
            ->sortByDesc(fn (array $h) => $h['similarity'])
            ->values();
    }

    public function admin()
    {
        $admins = User::all();

        return view('auth.admins.index', compact('admins'));
    }
}
