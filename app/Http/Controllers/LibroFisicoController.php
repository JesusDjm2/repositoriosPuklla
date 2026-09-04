<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFisica;
use App\Models\LibroFisico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibroFisicoController extends Controller
{
    public function index(Request $request)
    {
        $query = LibroFisico::query()
            ->with('categoriasFisicas')
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $term = '%'.addcslashes($request->input('q'), '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('autor', 'like', $term)
                    ->orWhere('codigo', 'like', $term);
            });
        }

        if ($request->filled('categoria_fisica_id')) {
            $cid = (int) $request->input('categoria_fisica_id');
            $query->whereHas('categoriasFisicas', function ($q) use ($cid) {
                $q->where('categorias_fisicas.id', $cid);
            });
        }

        if ($request->filled('ubicacion')) {
            $query->where('ubicacion', $request->input('ubicacion'));
        }

        $query->withCount(['prestamos as ejemplares_prestados_count' => fn ($q) => $q->activos()]);

        $libros = $query->paginate(25)->withQueryString();
        $categoriasFiltro = CategoriaFisica::orderBy('nombre')->get();

        return view('libros-fisicos.index', compact('libros', 'categoriasFiltro'));
    }

    public function create()
    {
        $categoriasFisicas = CategoriaFisica::orderBy('nombre')->get();

        return view('libros-fisicos.create', compact('categoriasFisicas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'editorial' => 'nullable|string|max:255',
            'anio_publicacion' => 'required|integer|min:1000|max:' . (date('Y') + 1),
            'numero_paginas' => 'nullable|integer|min:1|max:65535',
            'codigo' => 'required|string|max:100|unique:libros_fisicos,codigo',
            'cantidad_ejemplares' => 'required|integer|min:1|max:9999',
            'tipo_material' => ['required', Rule::in(array_keys(CategoriaFisica::TIPOS_MATERIAL))],
            'ubicacion' => ['required', Rule::in(array_keys(LibroFisico::UBICACIONES))],
            'categorias_fisicas' => 'required|array|min:1',
            'categorias_fisicas.*' => 'exists:categorias_fisicas,id',
        ]);

        $libro = LibroFisico::create($request->only(['nombre', 'autor', 'editorial', 'anio_publicacion', 'numero_paginas', 'codigo', 'cantidad_ejemplares', 'tipo_material', 'ubicacion']));
        $libro->categoriasFisicas()->sync($request->input('categorias_fisicas'));

        return redirect()->route('biblioteca-fisica.libros.index')
            ->with('success', 'Libro físico registrado correctamente.');
    }

    public function edit(LibroFisico $libro_fisico)
    {
        $categoriasFisicas = CategoriaFisica::orderBy('nombre')->get();
        $categoriasSeleccionadas = $libro_fisico->categoriasFisicas->pluck('id')->all();
        $prestadosActivos = $libro_fisico->prestamosActivos()->count();

        return view('libros-fisicos.edit', [
            'libro' => $libro_fisico,
            'categoriasFisicas' => $categoriasFisicas,
            'categoriasSeleccionadas' => $categoriasSeleccionadas,
            'prestadosActivos' => $prestadosActivos,
        ]);
    }

    public function update(Request $request, LibroFisico $libro_fisico)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'editorial' => 'nullable|string|max:255',
            'anio_publicacion' => 'required|integer|min:1000|max:' . (date('Y') + 1),
            'numero_paginas' => 'nullable|integer|min:1|max:65535',
            'codigo' => ['required', 'string', 'max:100', Rule::unique('libros_fisicos', 'codigo')->ignore($libro_fisico->id)],
            'cantidad_ejemplares' => 'required|integer|min:1|max:9999',
            'tipo_material' => ['required', Rule::in(array_keys(CategoriaFisica::TIPOS_MATERIAL))],
            'ubicacion' => ['required', Rule::in(array_keys(LibroFisico::UBICACIONES))],
            'categorias_fisicas' => 'required|array|min:1',
            'categorias_fisicas.*' => 'exists:categorias_fisicas,id',
        ]);

        $prestados = $libro_fisico->prestamosActivos()->count();
        if ($request->integer('cantidad_ejemplares') < $prestados) {
            return back()->withInput()->with('error',
                "No se puede reducir a {$request->input('cantidad_ejemplares')} ejemplares: actualmente hay {$prestados} prestados.");
        }

        $libro_fisico->update($request->only(['nombre', 'autor', 'editorial', 'anio_publicacion', 'numero_paginas', 'codigo', 'cantidad_ejemplares', 'tipo_material', 'ubicacion']));
        $libro_fisico->categoriasFisicas()->sync($request->input('categorias_fisicas'));

        return redirect()->route('biblioteca-fisica.libros.index')
            ->with('success', 'Libro físico actualizado correctamente.');
    }

    public function destroy(LibroFisico $libro_fisico)
    {
        if ($libro_fisico->prestamos()->exists()) {
            return redirect()->route('biblioteca-fisica.libros.index')
                ->with('error', 'No se puede eliminar el libro mientras tenga préstamos registrados (activos o históricos).');
        }

        $libro_fisico->delete();

        return redirect()->route('biblioteca-fisica.libros.index')
            ->with('success', 'Libro físico eliminado correctamente.');
    }

    // ─── Vista pública del catálogo físico ───────────────────────────────────

    public function catalogo(Request $request)
    {
        $query = LibroFisico::query()
            ->with('categoriasFisicas')
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $term = '%' . addcslashes($request->input('q'), '%_\\') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('autor', 'like', $term)
                    ->orWhere('editorial', 'like', $term);
            });
        }

        if ($request->filled('cat')) {
            $cid = (int) $request->input('cat');
            $query->whereHas('categoriasFisicas', fn($q) => $q->where('categorias_fisicas.id', $cid));
        }

        $libros     = $query->paginate(24)->withQueryString();
        $categorias = CategoriaFisica::withCount('librosFisicos')
            ->has('librosFisicos')
            ->orderBy('nombre')
            ->get();

        $totalLibros = LibroFisico::count();

        return view('catalogo-fisico.index', compact('libros', 'categorias', 'totalLibros'));
    }

    // ─── Búsqueda AJAX (Select2) para elegir un libro sin cargar todo el catálogo ─

    public function buscarAjax(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = LibroFisico::query()->with('categoriasFisicas')->orderBy('nombre');

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(function ($q) use ($like) {
                $q->where('nombre', 'like', $like)
                    ->orWhere('autor', 'like', $like)
                    ->orWhere('codigo', 'like', $like);
            });
        }

        $query->withCount(['prestamos as ejemplares_prestados_count' => fn ($q) => $q->activos()]);

        $total = (clone $query)->count();
        $libros = $query->forPage($page, $perPage)->get();

        return response()->json([
            'results' => $libros->map(fn (LibroFisico $l) => $this->libroParaSelect2($l)),
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }

    /**
     * Representación de un libro físico para Select2: además de id/text trae
     * todos los campos del libro para poder pintar la tarjeta de detalle
     * en el formulario de préstamos sin pedirlos de nuevo al servidor.
     */
    private function libroParaSelect2(LibroFisico $l): array
    {
        return [
            'id' => $l->id,
            'text' => "{$l->nombre} — {$l->autor} ({$l->ejemplares_disponibles} disp.)",
            'disabled' => $l->ejemplares_disponibles < 1,
            'nombre' => $l->nombre,
            'autor' => $l->autor,
            'editorial' => $l->editorial,
            'anio_publicacion' => $l->anio_publicacion,
            'numero_paginas' => $l->numero_paginas,
            'codigo' => $l->codigo,
            'cantidad_ejemplares' => $l->cantidad_ejemplares,
            'ejemplares_disponibles' => $l->ejemplares_disponibles,
            'ubicacion' => LibroFisico::nombreParaUbicacion($l->ubicacion),
            'tipo_material' => $l->tipo_material ? [
                'nombre' => CategoriaFisica::nombreParaTipoMaterial($l->tipo_material),
                'color' => CategoriaFisica::colorForTipoMaterial($l->tipo_material),
            ] : null,
            'categorias' => $l->categoriasFisicas->map(fn (CategoriaFisica $c) => [
                'nombre' => $c->nombre,
                'color' => CategoriaFisica::colorForCodigo($c->codigo),
            ])->values(),
        ];
    }

    // ─── Sugerencia de código basada en la categoría / Dewey ─────────────────

    public function sugerirCodigo(Request $request): JsonResponse
    {
        $prefix = $this->prefijoDesdeCategorias(
            array_filter(array_map('intval', (array) $request->input('categorias', [])))
        );

        $maximo = LibroFisico::where('codigo', 'like', $prefix . '-%')
            ->get(['codigo'])
            ->map(function ($l) use ($prefix) {
                $parte = substr($l->codigo, strlen($prefix) + 1);
                return (int) ltrim($parte, '0') ?: 0;
            })
            ->max() ?? 0;

        $codigo = $prefix . '-' . str_pad($maximo + 1, 4, '0', STR_PAD_LEFT);

        // Asegurar que no supere 50 chars (límite de la columna)
        return response()->json(['codigo' => mb_substr($codigo, 0, 50)]);
    }

    private function prefijoDesdeCategorias(array $ids): string
    {
        if (empty($ids)) {
            return 'PF-' . date('Y');
        }

        // Tomar la primera categoría que tenga código
        $cat = CategoriaFisica::whereIn('id', $ids)
            ->whereNotNull('codigo')
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->first();

        if (!$cat) {
            return 'PF-' . date('Y');
        }

        $codigo = $cat->codigo; // ej: "DEW-300", "TEX-PRI", "REF", "BIB-GEN"

        // Si es Dewey (DEW-NNN), usar solo el número como prefijo: "300"
        if (preg_match('/^DEW-(\d+)$/', $codigo, $m)) {
            return $m[1];
        }

        // Cualquier otra categoría (TEX-PRI, REF, DOC, SW…): usar su código tal cual
        return $codigo;
    }
}
