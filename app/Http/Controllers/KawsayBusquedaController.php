<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayLibro;
use App\Models\KawsayTema;
use App\Models\KawsayVideo;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Búsqueda SOLO dentro de Kawsay (áreas, temas, archivos de contenido,
 * libros y videos). La búsqueda de todos los repositorios sigue viviendo en
 * EnlacesController::busqueda (buscador del pie de página); las dos comparten
 * el mismo criterio de coincidencia (contiene el texto, o es muy parecido:
 * tolera errores de tipeo).
 */
class KawsayBusquedaController extends Controller
{
    private const MAX_POR_GRUPO = 60;

    public function buscar(Request $request)
    {
        $query = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $request->input('query', ''))) ?? '');

        $vista = ['query' => $query, 'corto' => mb_strlen($query) < 3, 'grupos' => collect(), 'total' => 0];

        if ($vista['corto']) {
            $vista['areasLista'] = KawsayArea::orderBy('orden')->get(['nombre', 'slug']);

            return view('kawsay.buscar', $vista);
        }

        $needle = EnlacesController::needleNorm($query);
        $tipos = KawsayTaxonomia::tiposContenido();

        // Cada elemento: título, meta, enlace, ícono y puntaje (100 = contiene el texto exacto).
        $puntaje = fn (?string ...$campos) => collect($campos)->map(fn ($c) => EnlacesController::fuzzyBestPercent($needle, $c))->max() ?? 0;
        $coincide = fn (?string ...$campos) => collect($campos)->contains(fn ($c) => EnlacesController::fieldMatches($needle, $c));

        $areas = KawsayArea::orderBy('orden')->get()
            ->filter(fn ($a) => $coincide($a->nombre, $a->descripcion))
            ->map(fn ($a) => [
                'titulo' => $a->nombre,
                'meta' => Str::limit((string) $a->descripcion, 110),
                'href' => route('kawsay.area', $a->slug),
                'externo' => false,
                'score' => $puntaje($a->nombre, $a->descripcion),
            ]);

        $temas = KawsayTema::with('area')->orderBy('orden')->get()
            ->filter(fn ($t) => $t->area && $coincide($t->titulo, $t->descripcion))
            ->map(fn ($t) => [
                'titulo' => $t->titulo,
                'meta' => $t->area->nombre.($t->descripcion ? ' · '.Str::limit($t->descripcion, 90) : ''),
                'href' => route('kawsay.tema', [$t->area->slug, $t->slug]),
                'externo' => false,
                'score' => $puntaje($t->titulo, $t->descripcion),
            ]);

        $archivos = KawsayArchivo::with('tema.area')->latest()->get()
            ->filter(fn ($f) => $f->tema && $f->tema->area && $coincide($f->titulo, $f->nombre_original))
            ->map(fn ($f) => [
                'titulo' => $f->titulo,
                'meta' => ($tipos[$f->tipo_contenido]['label'] ?? $f->tipo_contenido).' · '.$f->tema->titulo,
                'href' => route('kawsay.contenido', [$f->tema->area->slug, $f->tema->slug, $f->tipo_contenido]),
                'externo' => false,
                'icono' => $tipos[$f->tipo_contenido]['icono'] ?? 'fa-file',
                'score' => $puntaje($f->titulo, $f->nombre_original),
            ]);

        $libros = KawsayLibro::cursor()
            ->filter(fn ($l) => $coincide($l->nombre, $l->autor))
            ->map(fn ($l) => [
                'titulo' => $l->nombre,
                'meta' => $l->autor,
                'href' => 'https://drive.google.com/file/d/'.$l->identificador.'/preview',
                'externo' => true,
                'score' => $puntaje($l->nombre, $l->autor),
            ])->collect();

        $videos = KawsayVideo::cursor()
            ->filter(fn ($v) => $coincide($v->nombre, $v->descripcion))
            ->map(fn ($v) => [
                'titulo' => $v->nombre,
                'meta' => Str::limit((string) $v->descripcion, 100),
                'href' => $v->youtube
                    ? 'https://www.youtube.com/watch?v='.$v->youtube
                    : ($v->drive ? 'https://drive.google.com/file/d/'.$v->drive.'/view' : null),
                'externo' => true,
                'score' => $puntaje($v->nombre, $v->descripcion),
            ])->collect();

        $definiciones = [
            ['clave' => 'areas', 'titulo' => 'Áreas de trabajo', 'icono' => 'fa-seedling', 'color' => '#6e7d1e', 'items' => $areas],
            ['clave' => 'temas', 'titulo' => 'Temas', 'icono' => 'fa-map-signs', 'color' => '#2f6f73', 'items' => $temas],
            ['clave' => 'archivos', 'titulo' => 'Contenido de los temas', 'icono' => 'fa-file-lines', 'color' => '#a8562a', 'items' => $archivos],
            ['clave' => 'libros', 'titulo' => 'Libros', 'icono' => 'fa-book', 'color' => '#7a3f6d', 'items' => $libros],
            ['clave' => 'videos', 'titulo' => 'Videos', 'icono' => 'fa-circle-play', 'color' => '#b42525', 'items' => $videos],
        ];

        $grupos = collect($definiciones)
            ->map(function ($g) {
                /** @var Collection $items */
                $items = collect($g['items'])->sortByDesc('score')->values();
                $g['total'] = $items->count();
                $g['items'] = $items->take(self::MAX_POR_GRUPO);

                return $g;
            })
            ->filter(fn ($g) => $g['total'] > 0)
            ->values();

        $vista['grupos'] = $grupos;
        $vista['total'] = $grupos->sum('total');
        $vista['areasLista'] = KawsayArea::orderBy('orden')->get(['nombre', 'slug']);

        return view('kawsay.buscar', $vista);
    }
}
