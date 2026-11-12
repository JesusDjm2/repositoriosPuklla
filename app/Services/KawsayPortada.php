<?php

namespace App\Services;

use App\Models\KawsayArchivo;
use App\Models\KawsayLibro;
use App\Models\KawsayTema;
use App\Models\KawsayVideo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;

/**
 * Datos visuales de Kawsay: carruseles de la portada (uno por formato, más
 * "Lo más visto"), el "Tema del mes" y las tarjetas del catálogo dentro de
 * cada tema.
 *
 * Une dos orígenes con distinta forma — los archivos de cada tema
 * (KawsayArchivo) y el catálogo antiguo (KawsayLibro / KawsayVideo) — en un
 * mismo formato de tarjeta (partials/carrusel-card), para que las vistas no
 * tengan que saber de dónde viene cada cosa. Cada tarjeta lleva su acción ya
 * resuelta (visor de PDF, reproductor o galería) sin salir de la página.
 *
 * Las columnas nuevas (portada, vistas, destacado, kawsay_tema_id del catálogo)
 * se usan solo si existen: el código no rompe si en producción aún no se
 * ejecutó database/migracion_produccion_kawsay_20261109.sql.
 */
class KawsayPortada
{
    /** Cuántas tarjetas carga cada carrusel. */
    private const POR_CARRUSEL = 12;

    /** "Lo más visto" solo aparece con al menos esta cantidad de archivos vistos. */
    private const MIN_POPULARES = 4;

    private const ETIQUETA_CONTENIDO = [
        'ficha' => 'Ficha',
        'teoria' => 'Teoría',
        'bibliografia' => 'Bibliografía',
        'experiencia' => 'Experiencia',
        'mapa' => 'Mapa',
        'presentacion' => 'Presentación',
        'galeria' => 'Galería',
        'video' => 'Video',
        'audio' => 'Audio',
    ];

    private const CON_TEMA = ['tema:id,kawsay_area_id,titulo,slug', 'tema.area:id,nombre,slug,color'];

    /**
     * @return array<string, array{titulo:string, subtitulo:string, icono:string, items:Collection, verTodo:?array}>
     *         Solo formatos con contenido, en el orden en que se muestran.
     */
    public function carruseles(): array
    {
        $catalogoConTema = Contador::columnaExiste('kawsay_videos', 'kawsay_tema_id');

        $videos = KawsayArchivo::with(self::CON_TEMA)->has('tema')->where('tipo', 'video')
            ->latest()->take(self::POR_CARRUSEL)->get()
            ->map(fn (KawsayArchivo $a) => $this->itemArchivo($a))
            ->concat(KawsayVideo::with(array_merge(['categoria:id,nombre,url'], $catalogoConTema ? self::CON_TEMA : []))
                ->latest()->take(self::POR_CARRUSEL)->get()
                ->map(fn (KawsayVideo $v) => $this->itemVideoCatalogo($v)))
            ->sortByDesc('fecha')->take(self::POR_CARRUSEL)->values();

        $fotos = KawsayArchivo::with(self::CON_TEMA)->has('tema')->where('tipo', 'imagen')
            ->latest()->take(self::POR_CARRUSEL + 4)->get()->values()
            // visorIndice: abre el visor de galería (kawsay.temas.partials.visor-galeria) en esta foto
            ->map(fn (KawsayArchivo $a, int $i) => $this->itemArchivo($a, $i));

        $audios = KawsayArchivo::with(self::CON_TEMA)->has('tema')->where('tipo', 'audio')
            ->latest()->take(self::POR_CARRUSEL)->get()
            ->map(fn (KawsayArchivo $a) => $this->itemArchivo($a));

        $documentos = KawsayArchivo::with(self::CON_TEMA)->has('tema')->whereIn('tipo', ['pdf', 'doc'])
            ->latest()->take(self::POR_CARRUSEL)->get()
            ->map(fn (KawsayArchivo $a) => $this->itemArchivo($a));

        $libros = KawsayLibro::with(Contador::columnaExiste('kawsay_libros', 'kawsay_tema_id') ? self::CON_TEMA : [])
            ->latest()->take(self::POR_CARRUSEL)->get()
            ->map(fn (KawsayLibro $l) => $this->itemLibro($l));

        $grupos = [
            'populares' => ['titulo' => 'Lo más visto', 'subtitulo' => 'Lo que más están abriendo docentes y estudiantes.',
                'icono' => 'fa-fire', 'items' => $this->populares(), 'verTodo' => null],
            'videos' => ['titulo' => 'Videos', 'subtitulo' => 'Historias que inspiran a cuidar la vida que nos rodea.',
                'icono' => 'fa-play-circle', 'items' => $videos,
                'verTodo' => ['url' => route('kawsayvideos.lista'), 'label' => 'Ver todos los videos']],
            'audios' => ['titulo' => 'Audios y testimonios', 'subtitulo' => 'Voces de la comunidad: relatos, canciones y testimonios.',
                'icono' => 'fa-headphones', 'items' => $audios, 'verTodo' => null],
            'fotos' => ['titulo' => 'Fotografías', 'subtitulo' => 'Momentos y saberes del territorio, en imágenes.',
                'icono' => 'fa-camera', 'items' => $fotos, 'verTodo' => null],
            'documentos' => ['titulo' => 'Fichas y documentos', 'subtitulo' => 'Fichas, teoría, bibliografía y experiencias para el aula.',
                'icono' => 'fa-file-pdf', 'items' => $documentos, 'verTodo' => null],
            'libros' => ['titulo' => 'Libros', 'subtitulo' => 'Páginas que siembran conciencia y respeto por la naturaleza.',
                'icono' => 'fa-book', 'items' => $libros,
                'verTodo' => ['url' => route('kawsaylibros.lista'), 'label' => 'Ver todos los libros']],
        ];

        return array_filter($grupos, fn ($g) => $g['items']->isNotEmpty());
    }

    /**
     * "Tema del mes" elegido en el panel (Kawsay-Temas → estrella), con lo que
     * trae por formato para mostrarlo como tarjeta grande. null si no hay.
     */
    public function temaDelMes(): ?object
    {
        if (! Contador::columnaExiste('kawsay_temas', 'destacado')) {
            return null;
        }

        $tema = KawsayTema::with('area:id,nombre,slug,color,imagen')
            ->withCount('archivos')
            ->where('destacado', true)->first();
        if (! $tema || ! $tema->area) {
            return null;
        }

        $formatos = $tema->archivos()
            ->selectRaw('tipo_contenido, count(*) as total')
            ->groupBy('tipo_contenido')->pluck('total', 'tipo_contenido');

        return (object) [
            'tema' => $tema,
            'area' => $tema->area,
            'url' => route('kawsay.tema', [$tema->area->slug, $tema->slug]),
            'imagen' => $tema->imagen ? asset($tema->imagen) : ($tema->area->imagen ? asset($tema->area->imagen) : null),
            'formatos' => collect(KawsayTaxonomia::tiposContenido())
                ->filter(fn ($t, $slug) => ($formatos[$slug] ?? 0) > 0)
                ->map(fn ($t, $slug) => (object) ['label' => $t['label'], 'icono' => $t['icono'], 'total' => $formatos[$slug]]),
        ];
    }

    /** Fotos en el formato que espera el visor de galería (id, titulo, drive_id). */
    public function fotosParaVisor(array $carruseles): Collection
    {
        return collect($carruseles['fotos']['items'] ?? [])
            ->map(fn ($f) => (object) ['id' => $f->id, 'titulo' => $f->titulo, 'drive_id' => $f->driveId]);
    }

    /** Libros y videos del catálogo asignados a un tema, como tarjetas. */
    public function catalogoDeTema(KawsayTema $tema): Collection
    {
        $items = collect();
        if (Contador::columnaExiste('kawsay_videos', 'kawsay_tema_id')) {
            $items = $items->concat($tema->videosCatalogo()->with('categoria:id,nombre,url')->latest()->get()
                ->map(fn (KawsayVideo $v) => $this->itemVideoCatalogo($v, enTema: true)));
        }
        if (Contador::columnaExiste('kawsay_libros', 'kawsay_tema_id')) {
            $items = $items->concat($tema->libros()->latest()->get()
                ->map(fn (KawsayLibro $l) => $this->itemLibro($l, enTema: true)));
        }

        return $items->values();
    }

    /** Fecha relativa en español ("hace 3 días") para las tarjetas. */
    public static function hace(?Carbon $fecha): string
    {
        return $fecha ? $fecha->locale('es')->diffForHumans() : '';
    }

    /** Archivos con más vistas (mezcla de formatos). Vacío si aún hay pocos. */
    private function populares(): Collection
    {
        if (! Contador::columnaExiste('kawsay_archivos', 'vistas')) {
            return collect();
        }

        $archivos = KawsayArchivo::with(self::CON_TEMA)->has('tema')
            ->where('vistas', '>', 0)->orderByDesc('vistas')->latest()
            ->take(self::POR_CARRUSEL)->get();

        if ($archivos->count() < self::MIN_POPULARES) {
            return collect();
        }

        // Aquí las fotos se abren en el visor de Drive (no están en la galería de la portada).
        return $archivos->map(fn (KawsayArchivo $a) => $this->itemArchivo($a, null, conVistas: true));
    }

    /**
     * Tarjeta de un archivo de tema. $visorIndice: posición en la galería de la
     * portada (solo fotos del carrusel de Fotografías).
     */
    public function itemArchivo(KawsayArchivo $a, ?int $visorIndice = null, bool $conVistas = false): object
    {
        $tema = $a->tema;
        $area = $tema?->area;
        $titulo = $a->titulo ?: pathinfo((string) $a->nombre_original, PATHINFO_FILENAME);

        [$formato, $accion] = match (true) {
            // YouTube: el modal carga el reproductor de YouTube recién al abrirlo.
            $a->esYoutube() => ['video', 'abrirVistaPreviaYoutube(' . Js::from($a->youtube_id) . ', ' . Js::from($titulo) . ')'],
            // Audio: el mismo reproductor del modal (un <video> reproduce MP3/M4A sin problema).
            $a->tipo === 'audio' => ['audio', 'abrirVistaPreviaVideo(' . Js::from(route('kawsay.archivo.video.stream', $a)) . ', '
                . Js::from($titulo) . ', ' . Js::from($a->getUrl()) . ')'],
            $a->tipo === 'video' => ['video', 'abrirVistaPreviaVideo(' . Js::from(route('kawsay.archivo.video.stream', $a)) . ', '
                . Js::from($titulo) . ', ' . Js::from($a->getUrl()) . ')'],
            $a->tipo === 'imagen' => ['foto', $visorIndice === null
                ? 'abrirVistaPreviaLibro(' . Js::from($a->drive_id) . ', ' . Js::from($titulo) . ', ' . Js::from('Abrir en nueva ventana') . ')'
                : null],
            default => ['documento', 'abrirVistaPreviaLibro(' . Js::from($a->drive_id) . ', ' . Js::from($titulo) . ', ' . Js::from('Abrir en nueva ventana') . ')'],
        };

        return (object) [
            'id' => $a->id,
            'driveId' => $a->drive_id,
            'formato' => $formato,
            'titulo' => $titulo,
            'etiqueta' => self::ETIQUETA_CONTENIDO[$a->tipo_contenido] ?? ucfirst((string) $a->tipo_contenido),
            'detalle' => $tema?->titulo,
            // Portada propia del video si la subieron; si no, la miniatura de Drive.
            // Un audio no tiene imagen: la tarjeta muestra su ícono (evita pedirle a Drive una miniatura que no existe).
            'thumb' => $formato === 'audio' && ! $a->portada ? null : $a->miniaturaUrl($formato === 'documento' ? 480 : 640),
            'fecha' => $a->created_at,
            'accion' => $accion,
            'visorIndice' => $visorIndice,
            'vistaUrl' => route('kawsay.archivo.vista', $a), // suma una vista al abrirlo
            'vistas' => $conVistas ? (int) $a->vistas : null,
            'area' => $area ? (object) ['nombre' => $area->nombre, 'color' => $area->color ?: '#6e7d1e'] : null,
            'temaTitulo' => $tema?->titulo,
            'temaUrl' => ($area && $tema) ? route('kawsay.contenido', [$area->slug, $tema->slug, $a->tipo_contenido]) : null,
        ];
    }

    public function itemLibro(KawsayLibro $l, bool $enTema = false): object
    {
        $tema = $l->relationLoaded('tema') ? $l->tema : null;

        return (object) [
            'id' => 'l' . $l->id,
            'formato' => 'libro',
            'titulo' => $l->nombre,
            'etiqueta' => 'Libro',
            'detalle' => $l->autor,
            'thumb' => "https://drive.google.com/thumbnail?id={$l->identificador}&sz=w480",
            'fecha' => $l->created_at,
            'accion' => 'abrirVistaPreviaLibro(' . Js::from($l->identificador) . ', ' . Js::from($l->nombre) . ')',
            'visorIndice' => null,
            'vistaUrl' => null,
            'vistas' => null,
            'area' => (! $enTema && $tema?->area) ? (object) ['nombre' => $tema->area->nombre, 'color' => $tema->area->color ?: '#6e7d1e'] : null,
            'temaTitulo' => $enTema ? null : $tema?->titulo,
            'temaUrl' => (! $enTema && $tema?->area) ? route('kawsay.tema', [$tema->area->slug, $tema->slug]) : null,
        ];
    }

    public function itemVideoCatalogo(KawsayVideo $v, bool $enTema = false): object
    {
        if ($v->youtube) {
            $thumb = "https://img.youtube.com/vi/{$v->youtube}/hqdefault.jpg";
            $accion = 'window.open(' . Js::from("https://www.youtube.com/watch?v={$v->youtube}") . ', "_blank", "noopener")';
        } else {
            $thumb = $v->drive ? GoogleDriveService::getThumbnailUrl($v->drive, 640) : null;
            $accion = $v->drive
                ? 'abrirVistaPreviaVideo(' . Js::from(route('kawsayvideos.stream', $v)) . ', ' . Js::from($v->nombre) . ', '
                    . Js::from(GoogleDriveService::getUrl($v->drive)) . ')'
                : null;
        }

        $tema = $v->relationLoaded('tema') ? $v->tema : null;
        $temaUrl = $tema?->area ? route('kawsay.tema', [$tema->area->slug, $tema->slug]) : null;

        return (object) [
            'id' => 'v' . $v->id,
            'formato' => 'video',
            'titulo' => $v->nombre,
            'etiqueta' => $v->youtube ? 'YouTube' : 'Video',
            'detalle' => $v->categoria?->nombre,
            'thumb' => $thumb,
            'fecha' => $v->created_at,
            'accion' => $accion,
            'visorIndice' => null,
            'vistaUrl' => null,
            'vistas' => null,
            'area' => (! $enTema && $tema?->area) ? (object) ['nombre' => $tema->area->nombre, 'color' => $tema->area->color ?: '#6e7d1e'] : null,
            // Con tema asignado, la tarjeta lleva a su tema; si no, a su categoría del catálogo.
            'temaTitulo' => $enTema ? null : ($tema?->titulo ?? $v->categoria?->nombre),
            'temaUrl' => $enTema ? null : ($temaUrl ?? ($v->categoria ? route('kawsayvideoscat.show', $v->categoria->url) : null)),
        ];
    }
}
