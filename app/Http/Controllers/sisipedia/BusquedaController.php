<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\Category;
use App\Models\sisipedia\CategoryFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Búsqueda propia de Sisipedia (la global de toda la Asociación es la del pie de página).
 *
 * Filtra en SQL con LIKE por palabra (la collation utf8mb4_unicode_ci ya ignora tildes
 * y mayúsculas) y ordena por relevancia en PHP: los nombres en BD llevan la numeración
 * ontológica ("1.3.1 Agua"), así que la relevancia se calcula sobre display_name, cosa
 * que en SQL obligaría a REGEXP_REPLACE (depende de la versión del motor). Con ~4.000
 * filas en total, rankear solo los ids que coinciden es barato.
 */
class BusquedaController extends Controller
{
    private const POR_PAGINA = 15;

    private const TABS = ['registros', 'materiales', 'aportes'];

    /** Profundidad máxima del árbol (hoy 7 niveles) para armar la ruta "A › B › C". */
    private const ANCESTROS = 'parent.parent.parent.parent.parent.parent.parent';

    public function index(Request $request)
    {
        $q = $this->limpiar((string) $request->query('q', ''));
        $palabras = $this->palabras($q);

        if ($palabras === []) {
            return view('sisichakuna.buscar', [
                'q' => $q,
                'palabras' => [],
                'tab' => 'registros',
                'totales' => ['registros' => 0, 'materiales' => 0, 'aportes' => 0],
                'resultados' => null,
                'temas' => $this->temasPrincipales(),
                'areas' => collect(),
                'area' => null,
                'areaDe' => [],
                'areasPorId' => collect(),
            ]);
        }

        // Cada candidato trae el registro al que pertenece, para saber su área (raíz del árbol).
        $candidatos = [
            'registros' => $this->candidatosRegistros($palabras),
            'materiales' => $this->candidatosMateriales($palabras),
            'aportes' => $this->candidatosAportes($palabras),
        ];
        $raizDe = $this->mapaDeRaices();
        $areaDe = [];
        $ids = [];
        foreach ($candidatos as $t => $lista) {
            foreach ($lista as $id => $c) {
                $areaDe[$t][$id] = $c[2] ? ($raizDe[$c[2]] ?? null) : null;
            }
            $ids[$t] = $this->rankear($lista, $q);
        }

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : null;
        // Sin pestaña elegida: la primera que tenga resultados.
        $tab ??= collect(self::TABS)->first(fn ($t) => count($ids[$t]) > 0) ?? 'registros';

        // Áreas presentes en la pestaña actual (antes de filtrar), para los chips "¿De qué área?".
        $conteoAreas = array_count_values(array_filter(array_map(fn ($id) => $areaDe[$tab][$id] ?? null, $ids[$tab])));
        $areas = $this->areas()->filter(fn ($a) => isset($conteoAreas[$a->id]))
            ->each(fn ($a) => $a->resultados = $conteoAreas[$a->id])->values();

        // El filtro de área se aplica a todas las pestañas, así sus conteos cuentan lo mismo.
        $area = (int) $request->query('area', 0);
        $area = $this->areas()->contains('id', $area) ? $area : null;
        if ($area) {
            foreach ($ids as $t => $lista) {
                $ids[$t] = array_values(array_filter($lista, fn ($id) => ($areaDe[$t][$id] ?? null) === $area));
            }
        }
        $totales = array_map('count', $ids);

        // Una página fuera de rango (enlace viejo, filtro que achicó la lista) muestra la última.
        $pagina = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil($totales[$tab] / self::POR_PAGINA)));
        $idsPagina = array_slice($ids[$tab], ($pagina - 1) * self::POR_PAGINA, self::POR_PAGINA);

        $items = match ($tab) {
            'registros' => $this->cargarRegistros($idsPagina),
            'materiales' => $this->cargarMateriales($idsPagina),
            'aportes' => $this->cargarAportes($idsPagina),
        };

        $resultados = new LengthAwarePaginator($items, $totales[$tab], self::POR_PAGINA, $pagina, [
            'path' => $request->url(),
            'query' => array_filter(['q' => $q, 'tab' => $tab, 'area' => $area]),
        ]);

        $sinResultados = array_sum(array_map('count', $candidatos)) === 0;
        if ($sinResultados) {
            $this->registrarSinResultados($q);
        }

        return view('sisichakuna.buscar', [
            'q' => $q,
            'palabras' => $palabras,
            'tab' => $tab,
            'totales' => $totales,
            'resultados' => $resultados,
            'temas' => $sinResultados ? $this->temasPrincipales() : collect(),
            'areas' => $areas,
            'area' => $area,
            'areaDe' => $areaDe[$tab] ?? [],
            'areasPorId' => $this->areas()->keyBy('id'),
        ]);
    }

    /**
     * Las áreas del saber (registros raíz) con su color fijo, tomado de tintes andinos.
     * El color sigue el orden del área, no su id, para que no cambie si se recrean.
     */
    private function areas(): Collection
    {
        return $this->areasCache ??= Category::query()
            ->whereNull('parent_id')->where('is_active', true)->orderBy('order')
            ->get(['id', 'name'])
            ->values()
            ->each(function ($a, $i) {
                $a->nombre = self::nombre($a->name, true);
                $a->color = self::COLORES_AREA[$i % count(self::COLORES_AREA)];
            });
    }

    private ?Collection $areasCache = null;

    /** Verde chilca, ocre, q'olle, cochinilla, naranja, tuna, añil, agua, maíz morado, oliva, tierra. */
    public const COLORES_AREA = ['#3f7d4e', '#8a5a2b', '#8f6410', '#9c3d2e', '#a9521f', '#a8466b', '#3b4f8f', '#2f7470', '#6b5b95', '#5f6b3a', '#7a4a3a'];

    /** [id de registro => id de su área raíz], con una sola consulta liviana (~1.500 filas). */
    private function mapaDeRaices(): array
    {
        $padre = Category::query()->pluck('parent_id', 'id')->all();
        $raiz = [];
        foreach ($padre as $id => $p) {
            $cur = $id;
            for ($guard = 0; ($padre[$cur] ?? null) && $guard < 12; $guard++) {
                $cur = $padre[$cur];
            }
            $raiz[$id] = $cur;
        }

        return $raiz;
    }

    /**
     * Nombre legible para la búsqueda: corrige entidades HTML guardadas sin ";" (p. ej.
     * "Comunicaci&oacuten") y, en las áreas, quita el numeral romano ("VII. Cosmovisión").
     */
    public static function nombre(?string $nombre, bool $esArea = false): string
    {
        $n = Category::stripLegacyOntologyPrefix((string) $nombre);
        $n = preg_replace('/&([aeiouAEIOU]acute|[nN]tilde|[uU]uml)(?!;)/', '&$1;', $n);
        $n = html_entity_decode($n, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $esArea ? (preg_replace('/^[IVXLC]+\.\s*/u', '', $n) ?: $n) : $n;
    }

    /** Área raíz de un registro con sus ancestros cargados. */
    public static function raiz(?Category $category): ?Category
    {
        for ($guard = 0; $category && $category->parent && $guard < 10; $guard++) {
            $category = $category->parent;
        }

        return $category;
    }

    /** Ruta "Padre › Hijo" sin el área raíz (que ya se muestra en su chip de color). */
    public static function rutaSinArea(?Category $category): string
    {
        $partes = [];
        for ($c = $category, $guard = 0; $c && $c->parent && $guard < 10; $c = $c->parent, $guard++) {
            array_unshift($partes, self::nombre($c->name));
        }

        return implode(' › ', $partes);
    }

    /** Sugerencias mientras se escribe (JSON liviano: 6 registros + 3 materiales). */
    public function sugerencias(Request $request)
    {
        $q = $this->limpiar((string) $request->query('q', ''));
        $palabras = $this->palabras($q);

        if ($palabras === [] || mb_strlen($q) < 2) {
            return response()->json(['registros' => [], 'materiales' => []]);
        }

        $registros = $this->cargarRegistros(array_slice($this->rankear($this->candidatosRegistros($palabras), $q), 0, 6));
        $materiales = $this->cargarMateriales(array_slice($this->rankear($this->candidatosMateriales($palabras), $q), 0, 3));

        return response()->json([
            'registros' => $registros->map(fn (Category $c) => [
                'nombre' => $c->display_name,
                'ruta' => $this->ruta($c->parent),
                'url' => route('sisipedia.categories.show', $c),
            ])->values(),
            'materiales' => $materiales->map(fn (CategoryFile $f) => [
                'nombre' => $f->nombre_display,
                'tipo' => $f->tipo,
                'ruta' => $this->ruta($f->category),
                'url' => route('sisipedia.categories.show', $f->category_id),
            ])->values(),
        ])->header('Cache-Control', 'public, max-age=60');
    }

    /** Panel: términos buscados que no devolvieron nada (para saber qué contenido falta). */
    public function sinResultados()
    {
        $terminos = Schema::hasTable('sisipedia_busquedas_sin_resultados')
            ? DB::table('sisipedia_busquedas_sin_resultados')->orderByDesc('veces')->orderByDesc('ultima_vez')->paginate(50)
            : null;

        return view('sisichakuna.busquedas-sin-resultados', compact('terminos'));
    }

    /** Ruta legible "Abuelo › Padre" a partir de un registro con sus ancestros cargados. */
    public static function ruta(?Category $category): string
    {
        $partes = [];
        for ($c = $category, $guard = 0; $c && $guard < 10; $c = $c->parent, $guard++) {
            array_unshift($partes, self::nombre($c->name, $c->parent_id === null));
        }

        return implode(' › ', $partes);
    }

    /**
     * Resalta las palabras buscadas (sin importar tildes ni mayúsculas) en un texto ya
     * escapado para HTML. Marca la palabra completa que empieza con lo buscado ("Aguas",
     * no "Agua"+"s") y no marca pedazos en medio de otra palabra. Devuelve HTML seguro.
     */
    public static function resaltar(?string $texto, array $palabras): string
    {
        $html = e((string) $texto);
        if ($palabras === [] || $html === '') {
            return $html;
        }

        $variantes = ['a' => 'aáàäâ', 'e' => 'eéèëê', 'i' => 'iíìïî', 'o' => 'oóòöô', 'u' => 'uúùüû', 'n' => 'nñ'];
        $patrones = array_map(function ($p) use ($variantes) {
            return implode('', array_map(
                fn ($ch) => isset($variantes[$ch]) ? '['.$variantes[$ch].']' : preg_quote($ch, '/'),
                mb_str_split(Str::ascii($p))
            ));
        }, $palabras);

        return preg_replace('/(?<![\p{L}\p{N}])((?:'.implode('|', $patrones).')[\p{L}\p{N}]*)/iu', '<mark>$1</mark>', $html) ?? $html;
    }

    /** Recorte de ~160 caracteres de un texto largo (HTML del editor) alrededor de la primera coincidencia. */
    public static function fragmento(?string $texto, array $palabras, int $largo = 160): string
    {
        $plano = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $texto))));
        if (mb_strlen($plano) <= $largo) {
            return $plano;
        }

        // Str::ascii conserva la longitud de las letras con tilde del español, así que la
        // posición en el texto normalizado sirve para el original.
        $normal = mb_strtolower(Str::ascii($plano));
        $pos = collect($palabras)
            ->map(fn ($p) => mb_strpos($normal, mb_strtolower(Str::ascii($p))))
            ->filter(fn ($p) => $p !== false)
            ->min() ?? 0;

        $inicio = max(0, $pos - 50);
        $recorte = mb_substr($plano, $inicio, $largo);

        return ($inicio > 0 ? '…' : '').$recorte.($inicio + $largo < mb_strlen($plano) ? '…' : '');
    }

    /* ── Candidatos: [id => [texto principal, texto secundario, id del registro]] ── */

    private function candidatosRegistros(array $palabras): array
    {
        return Category::query()
            ->where('is_active', true)
            ->where(fn (Builder $q) => $this->cadaPalabra($q, $palabras, ['name', 'description']))
            ->get(['id', 'name', 'description'])
            ->mapWithKeys(fn ($c) => [$c->id => [$c->display_name, (string) $c->description, $c->id]])
            ->all();
    }

    private function candidatosMateriales(array $palabras): array
    {
        return CategoryFile::query()
            ->whereHas('category', fn ($c) => $c->where('is_active', true))
            ->where(fn (Builder $q) => $this->cadaPalabra($q, $palabras, ['nombre_original']))
            ->get(['id', 'nombre_original', 'category_id'])
            ->mapWithKeys(fn ($f) => [$f->id => [(string) $f->nombre_original, '', $f->category_id]])
            ->all();
    }

    private function candidatosAportes(array $palabras): array
    {
        return Aportacion::query()
            ->where('is_approved', true)
            ->where(fn (Builder $q) => $this->cadaPalabra($q, $palabras, ['titulo', 'detalle', 'nombre_ol', 'institucion', 'ubicacion']))
            ->get(['id', 'titulo', 'nombre_ol', 'detalle', 'category_id'])
            ->mapWithKeys(fn ($a) => [$a->id => [(string) ($a->titulo ?: $a->nombre_ol), strip_tags((string) $a->detalle), $a->category_id]])
            ->all();
    }

    /** Cada palabra debe aparecer en al menos uno de los campos (AND entre palabras). */
    private function cadaPalabra(Builder $query, array $palabras, array $campos): void
    {
        foreach ($palabras as $p) {
            $like = '%'.addcslashes($p, '%_\\').'%';
            $query->where(function (Builder $w) use ($campos, $like) {
                foreach ($campos as $campo) {
                    $w->orWhere($campo, 'like', $like);
                }
            });
        }
    }

    /**
     * Ordena ids por relevancia: nombre igual > empieza con la frase > la contiene como
     * palabra > la contiene > solo coincide en texto secundario. Desempata el nombre más corto.
     *
     * @param  array<int, array{0:string,1:string}>  $candidatos
     * @return array<int, int>
     */
    private function rankear(array $candidatos, string $q): array
    {
        $frase = $this->normalizar($q);
        $puntaje = [];

        foreach ($candidatos as $id => [$principal, $secundario]) {
            $n = $this->normalizar($principal);
            $nivel = match (true) {
                $n === $frase => 0,
                str_starts_with($n, $frase) => 1,
                (bool) preg_match('/\b'.preg_quote($frase, '/').'\b/u', $n) => 2,
                str_contains($n, $frase) => 3,
                str_contains($this->normalizar($secundario), $frase) => 4,
                default => 5,
            };
            $puntaje[$id] = [$nivel, mb_strlen($n), $n];
        }

        uasort($puntaje, fn ($a, $b) => $a <=> $b);

        return array_keys($puntaje);
    }

    /* ── Carga de la página actual, respetando el orden de $ids ───────────── */

    private function cargarRegistros(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Category::query()
            ->whereIn('id', $ids)
            ->with([self::ANCESTROS => fn ($q) => $q->select('id', 'name', 'parent_id'), 'files:id,category_id,tipo'])
            ->withCount([
                'children' => fn ($q) => $q->where('is_active', true),
                'aportaciones' => fn ($q) => $q->where('is_approved', true),
            ])
            ->get()
            ->sortBy(fn ($c) => array_search($c->id, $ids, true))
            ->values();
    }

    private function cargarMateriales(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return CategoryFile::query()
            ->whereIn('id', $ids)
            ->with(['category.'.self::ANCESTROS => fn ($q) => $q->select('id', 'name', 'parent_id'), 'category:id,name,parent_id'])
            ->get()
            ->sortBy(fn ($f) => array_search($f->id, $ids, true))
            ->values();
    }

    private function cargarAportes(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Aportacion::query()
            ->whereIn('id', $ids)
            ->with(['category.'.self::ANCESTROS => fn ($q) => $q->select('id', 'name', 'parent_id'), 'category:id,name,parent_id', 'archivos:id,aportacion_id,tipo'])
            ->get()
            ->sortBy(fn ($a) => array_search($a->id, $ids, true))
            ->values();
    }

    /* ── Utilidades ──────────────────────────────────────────────────────── */

    private function limpiar(string $q): string
    {
        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($q))), 120, '');
    }

    /** Palabras de 2+ letras (máx. 6) para el filtro AND. */
    private function palabras(string $q): array
    {
        return collect(explode(' ', $q))
            ->map(fn ($p) => trim($p, " \t.,;:¿?¡!\"'()"))
            ->filter(fn ($p) => mb_strlen($p) >= 2)
            ->unique(fn ($p) => $this->normalizar($p))
            ->take(6)
            ->values()
            ->all();
    }

    private function normalizar(string $s): string
    {
        return mb_strtolower(Str::ascii($s));
    }

    private function temasPrincipales(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'slug']);
    }

    /** Guarda el término (normalizado) solo si existe la tabla; nunca rompe la búsqueda. */
    private function registrarSinResultados(string $q): void
    {
        try {
            if (! Schema::hasTable('sisipedia_busquedas_sin_resultados')) {
                return;
            }
            $termino = Str::limit($this->normalizar($q), 120, '');
            $actualizadas = DB::table('sisipedia_busquedas_sin_resultados')
                ->where('termino', $termino)
                ->update(['veces' => DB::raw('veces + 1'), 'ultima_vez' => now(), 'updated_at' => now()]);
            if ($actualizadas === 0) {
                DB::table('sisipedia_busquedas_sin_resultados')->insert([
                    'termino' => $termino, 'veces' => 1, 'ultima_vez' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
