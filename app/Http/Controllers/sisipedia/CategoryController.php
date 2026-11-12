<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\AportacionArchivo;
use App\Models\sisipedia\Category;
use App\Models\sisipedia\CategoryFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    public function index()
    {
        // Árbol completo en memoria con pocas consultas. Antes cada fila pedía su
        // numeración (hermanos de cada nivel), su padre y el tamaño de su
        // sub-árbol por separado: ~12.000 consultas y ~10 s con 1.500 registros.
        [$tree, $numeracion, $tamSubarbol] = $this->arbolAdmin();

        // Archivos por registro y tipo, y descargas por registro (2 consultas).
        $resumenArchivos = [];
        foreach (DB::table('category_files')->selectRaw('category_id, tipo, count(*) as total')
            ->groupBy('category_id', 'tipo')->get() as $fila) {
            $resumenArchivos[$fila->category_id][$fila->tipo] = (int) $fila->total;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('category_files', 'descargas')) {
            foreach (DB::table('category_files')->selectRaw('category_id, sum(descargas) as total')
                ->groupBy('category_id')->get() as $fila) {
                $resumenArchivos[$fila->category_id]['descargas'] = (int) $fila->total;
            }
        }

        // Conteo de aportaciones por categoría (una sola consulta)
        $aportCounts = DB::table('aportaciones')
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // Totales globales de archivos por tipo para la leyenda
        $fileTypeCounts = DB::table('category_files')
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        // Lo más visto y descargado (vacío hasta que exista la columna en esta base).
        $masVistos = collect();
        $masDescargados = collect();
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'visitas')) {
            $masVistos = Category::where('visitas', '>', 0)->orderByDesc('visitas')->take(10)->get(['id', 'name', 'slug', 'visitas']);
            $masDescargados = CategoryFile::where('descargas', '>', 0)->with('category:id,name,slug')
                ->orderByDesc('descargas')->take(10)->get(['id', 'category_id', 'tipo', 'nombre_original', 'descargas']);
        }

        // Contadores del banner "Panel Sisichakunay" (esta vista ES el panel del rol).
        $conteosPanel = app(\App\Services\ProyectoEstadisticasService::class)->paraRol('sisicha');

        return view('sisichakuna.categorias.index', compact(
            'tree', 'numeracion', 'tamSubarbol', 'resumenArchivos', 'conteosPanel',
            'aportCounts', 'fileTypeCounts', 'masVistos', 'masDescargados'
        ));
    }

    /**
     * Carga TODOS los registros en una consulta y arma el árbol en memoria
     * (relaciones children/parent ya resueltas, sin consultas perezosas).
     *
     * @return array{0: \Illuminate\Support\Collection, 1: array<int,string>, 2: array<int,int>}
     *         [raíces, numeración "1.2.3" por id, tamaño del sub-árbol por id]
     */
    private function arbolAdmin(): array
    {
        $columnas = ['id', 'parent_id', 'name', 'slug', 'image', 'order', 'is_active'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'visitas')) {
            $columnas[] = 'visitas';
        }

        // Mismo orden que el accessor numbering (order; id como desempate estable).
        $todas = Category::query()->orderBy('order')->orderBy('id')->get($columnas)->keyBy('id');
        $porPadre = $todas->groupBy(fn ($c) => $c->parent_id ?? 0);

        foreach ($todas as $cat) {
            $cat->setRelation('children', ($porPadre[$cat->id] ?? collect())->values());
            $cat->setRelation('parent', $cat->parent_id ? $todas->get($cat->parent_id) : null);
        }

        $numeracion = [];
        $tamSubarbol = [];
        $recorrer = function ($hijos, string $prefijo) use (&$recorrer, &$numeracion, &$tamSubarbol): int {
            $total = 0;
            foreach ($hijos->values() as $i => $cat) {
                $numeracion[$cat->id] = $prefijo === '' ? (string) ($i + 1) : $prefijo . '.' . ($i + 1);
                $tamSubarbol[$cat->id] = 1 + $recorrer($cat->children, $numeracion[$cat->id]);
                $total += $tamSubarbol[$cat->id];
            }

            return $total;
        };

        $raices = ($porPadre[0] ?? collect())->values();
        $recorrer($raices, '');

        return [$raices, $numeracion, $tamSubarbol];
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="registros_sisipedia_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Árbol en memoria (3 consultas en total): numeración y ruta sin consultar
        // por fila. Antes: ~10.600 consultas y ~23 s, riesgo de corte en el hosting.
        [$raices, $numeracion] = $this->arbolAdmin();
        $categories = collect();
        $aplanar = function ($hijos, int $depth) use (&$aplanar, $categories): void {
            foreach ($hijos as $cat) {
                $cat->depth = $depth;
                $categories->push($cat);
                $aplanar($cat->children, $depth + 1);
            }
        };
        $aplanar($raices, 0);
        $categoriesList = $categories->keyBy('id');

        $callback = function () use ($categories, $categoriesList, $numeracion) {
            $file = fopen('php://output', 'w');

            // BOM para compatibilidad con Excel (UTF-8)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Fila de encabezado con punto y coma (;) como delimitador para Excel en español
            fputcsv($file, [
                'ID',
                'Numeración',
                'Nombre Completo',
                'Nombre Limpio',
                'Slug',
                'Ruta Jerárquica',
                'ID Padre',
                'Nombre Padre',
                'Estado (Activo)',
                'Orden',
                'Nivel Jerárquico'
            ], ';');

            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $numeracion[$category->id] ?? '',
                    $category->name,
                    $category->display_name,
                    $category->slug,
                    $category->path,
                    $category->parent_id ?? 'Ninguno',
                    ($category->parent_id && isset($categoriesList[$category->parent_id])) 
                        ? $categoriesList[$category->parent_id]->name 
                        : 'Ninguno',
                    $category->is_active ? 'Activo' : 'Inactivo',
                    $category->order,
                    $category->depth
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $parentTree = $this->buildParentTree();

        return view('sisichakuna.categorias.create', compact('parentTree'));
    }

    public function store(Request $request)
    {
        // Detect when PHP silently dropped the request body (post_max_size exceeded)
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return back()->withErrors(['video' => "El archivo supera el límite permitido ({$maxMb}MB)."]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'fecha' => 'nullable|date',
            'idioma' => 'nullable|in:'.implode(',', array_keys(Category::IDIOMAS)),
            'localidad' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'archivos' => 'nullable|array',
            'archivos.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,mp3,wav,ogg,mpeg,mp4,webm,mov,m4v',
                'max:307200',
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug(Category::stripLegacyOntologyPrefix($validated['name']));
        }

        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $slug = Str::slug(Category::stripLegacyOntologyPrefix($validated['name']));

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'), $slug);
        }

        $category = Category::create($validated);

        // Subir archivos múltiples a Google Drive
        $this->uploadFiles($request, $category, $slug);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Raíces del árbol público + su primer nivel. Los niveles más profundos se piden al
     * abrir cada carpeta (hijos()). Antes se renderizaba el árbol entero (~1.500 nodos,
     * ~3,8 MB de HTML y una consulta de conteo por nodo) en cada visita.
     */
    private function treeRoots()
    {
        return $this->nodosArbol()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $this->nodosArbol($q)])
            ->get();
    }

    /** Consulta base de un nivel del árbol público, con lo que pinta sisichakuna.tree. */
    private function nodosArbol($query = null)
    {
        return ($query ?? Category::query())
            ->where('is_active', true)
            ->select('id', 'name', 'slug', 'description', 'parent_id', 'order')
            ->withCount([
                'children as public_children_count' => fn ($q) => $q->where('is_active', true),
                'aportaciones' => fn ($q) => $q->where('is_approved', true),
            ])
            ->with('files:id,category_id,tipo')
            ->orderBy('order');
    }

    /** HTML de los hijos de un nodo del árbol (se inserta al expandirlo). */
    public function hijos(Request $request, Category $category)
    {
        abort_unless($category->is_active, 404);

        $hijos = $this->nodosArbol()->where('parent_id', $category->id)->get();
        $level = min(10, max(1, (int) $request->query('level', 1)));

        return response()
            ->view('sisichakuna.tree-children', compact('hijos', 'level'))
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function publicIndex()
    {
        $tree = $this->treeRoots();

        $aportacionesSueltas = Aportacion::with('archivos')
            ->whereNull('category_id')
            ->where('is_approved', true)
            ->latest()
            ->limit(20)
            ->get();

        return view('sisichakuna.show', compact('tree', 'aportacionesSueltas'));
    }

    public function show(Category $category)
    {
        if (! $category->is_active) {
            if (auth()->check()) {
                return redirect()->route('sisipedia.categories.admin-show', $category);
            }
            abort(404);
        }

        // Solo lo que pertenece a ESTE registro. Antes también se cargaban las
        // aportaciones sin registro (de la portada) y se listaban al pie de cada
        // registro, así que parecían pertenecer a todos; esas viven en
        // sisipedia.aportaciones.general.
        $category->load([
            'parent.parent.parent',
            'children' => fn ($q) => $q->where('is_active', true)
                ->withCount(['aportaciones' => fn ($a) => $a->where('is_approved', true)]),
            'children.files:id,category_id,tipo',
            'aportaciones' => fn ($q) => $q->where('is_approved', true)->latest(),
            'aportaciones.archivos',
            'files',
        ]);

        $breadcrumbs = collect();
        $current = $category;

        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }

        // Una visita por persona y sesión; no cuenta robots (ver ContadorSisipedia).
        \App\Services\ContadorSisipedia::visita($category, request());

        return view('sisichakuna.detalle', compact('category', 'breadcrumbs'));
    }

    public function adminShow(Category $category)
    {
        $category->load([
            'parent.parent.parent',
            'children.children',
            'aportaciones',
            'aportaciones.archivos',
            'files',
        ]);

        $breadcrumbs = collect();
        $current = $category;

        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }

        return view('sisichakuna.categorias.detalle', compact('category', 'breadcrumbs'));
    }

    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['categories' => [], 'aportaciones' => []]);
        }

        $categories = Category::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->with(['parent.parent.parent', 'files'])
            ->limit(20)
            ->get();

        $aportaciones = Aportacion::where(function ($query) use ($q) {
            $query->where('nombre_ol', 'like', "%{$q}%")
                ->orWhere('institucion', 'like', "%{$q}%")
                ->orWhere('ubicacion', 'like', "%{$q}%")
                ->orWhere('detalle', 'like', "%{$q}%");
        })
            ->with('category.parent.parent')
            ->limit(20)
            ->get();

        return response()->json([
            'categories' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->display_name,
                'description' => $c->description ? Str::limit($c->description, 110) : null,
                'url' => route('sisipedia.categories.show', $c),
                'breadcrumb' => $c->path,
                'image' => $c->image ? asset($c->image) : null,
                'pdf' => $c->files->where('tipo', 'pdf')->isNotEmpty(),
                'doc' => $c->files->where('tipo', 'doc')->isNotEmpty(),
                'audio' => $c->files->where('tipo', 'audio')->isNotEmpty(),
                'video' => $c->files->where('tipo', 'video')->isNotEmpty(),
                'imagen' => $c->files->where('tipo', 'imagen')->isNotEmpty(),
            ]),
            'aportaciones' => $aportaciones->map(fn ($a) => [
                'id' => $a->id,
                'nombre_ol' => $a->nombre_ol,
                'rol_nombre' => $a->rol_nombre,
                'institucion' => $a->institucion,
                'ubicacion' => $a->ubicacion,
                'detalle' => $a->detalle ? Str::limit($a->detalle, 110) : null,
                'pdf' => (bool) $a->pdf,
                'doc' => (bool) $a->doc,
                'audio' => (bool) $a->audio,
                'video' => (bool) $a->video,
                'category_name' => $a->category?->display_name,
                'category_url' => $a->category ? route('sisipedia.categories.show', $a->category) : null,
                'category_breadcrumb' => $a->category?->path,
            ]),
        ]);
    }

    public function registros(Request $request)
    {
        $tipo = $request->get('tipo');
        $tiposValidos = ['audio', 'video', 'imagen', 'pdf'];

        $archivoCounts = CategoryFile::whereIn('tipo', $tiposValidos)
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        if ($tipo && in_array($tipo, $tiposValidos)) {
            $q = trim((string) $request->get('q', ''));

            $archivos = CategoryFile::with([
                'category:id,slug,name,parent_id',
                'category.parent:id,slug,name,parent_id',
                'category.parent.parent:id,slug,name',
            ])
                ->where('tipo', $tipo)
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($w) use ($q) {
                        $w->where('nombre_original', 'like', "%{$q}%")
                            ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('category.parent', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('category.parent.parent', fn ($c) => $c->where('name', 'like', "%{$q}%"));
                    });
                })
                ->orderBy('category_id')
                ->orderBy('orden')
                ->paginate(24)
                ->withQueryString();

            return view('sisichakuna.show', compact('archivos', 'tipo', 'archivoCounts', 'q'));
        }

        // Enlaces viejos (?buscar=) apuntaban al filtro del árbol: ahora hay búsqueda propia.
        $buscar = trim((string) $request->get('buscar', ''));
        if ($buscar !== '') {
            return redirect()->route('sisipedia.buscar', ['q' => $buscar], 301);
        }

        $tree = $this->treeRoots();

        $aportacionesSueltas = Aportacion::with('archivos')
            ->whereNull('category_id')
            ->where('is_approved', true)
            ->latest()
            ->limit(20)
            ->get();

        return view('sisichakuna.show', compact('tree', 'aportacionesSueltas', 'archivoCounts'));
    }

    public function edit(Category $category)
    {
        $parentTree = $this->buildParentTree(exclude: $category->id);

        return view('sisichakuna.categorias.edit', compact('category', 'parentTree'));
    }

    public function update(Request $request, Category $category)
    {
        // Detect when PHP silently dropped the request body (post_max_size exceeded)
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return back()->withErrors(['video' => "El archivo supera el límite permitido ({$maxMb}MB)."]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'fecha' => 'nullable|date',
            'idioma' => 'nullable|in:'.implode(',', array_keys(Category::IDIOMAS)),
            'localidad' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'archivos' => 'nullable|array',
            'archivos.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,mp3,wav,ogg,mpeg,mp4,webm,mov,m4v',
                'max:307200',
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // No se puede asignar a sí mismo como padre
        if (($validated['parent_id'] ?? null) == $category->id) {
            return back()->with('error', 'No se puede asignar una categoría como padre de sí misma.');
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug(Category::stripLegacyOntologyPrefix($validated['name']));
        }

        if (empty($validated['order'])) {
            $validated['order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('order') + 1;
        }

        $slug = Str::slug(Category::stripLegacyOntologyPrefix($validated['name']));

        // --- Imagen ---
        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }
            $validated['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }
            $validated['image'] = $this->storeImage($request->file('image'), $slug);
        }

        $category->update($validated);

        // Subir archivos nuevos
        $this->uploadFiles($request, $category, $slug);

        return redirect()->route('sisipedia.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Request $request, Category $category)
    {
        $cascadeSubtree = $request->boolean('cascade_subtree');

        if ($category->children()->count() > 0 && ! $cascadeSubtree) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene subcategorías. Usa «Eliminar jerarquía» en el árbol o marca eliminación en cascada.');
        }

        DB::transaction(function () use ($category, $cascadeSubtree): void {
            if ($cascadeSubtree) {
                foreach ($category->subtreeIdsPostOrder() as $categoryId) {
                    $cat = Category::with(['aportaciones', 'files'])->find($categoryId);
                    if ($cat) {
                        $this->purgeCategoryStoredResources($cat);
                        $cat->delete();
                    }
                }

                return;
            }

            $this->purgeCategoryStoredResources($category);
            $category->delete();
        });

        return redirect()->route('sisipedia.categories.index')
            ->with(
                'success',
                $cascadeSubtree
                    ? 'Registro y toda su jerarquía eliminados correctamente.'
                    : 'Categoría eliminada exitosamente.'
            );
    }

    /**
     * Quita archivos locales, adjuntos en Drive y aportaciones de una categoría (sin borrar la fila aún).
     */
    private function purgeCategoryStoredResources(Category $category): void
    {
        foreach ($category->aportaciones as $aportacion) {
            foreach ($aportacion->archivos as $archivo) {
                $this->drive->delete($archivo->drive_id);
                $archivo->delete();
            }
            $aportacion->delete();
        }

        foreach ($category->files as $file) {
            $this->drive->delete($file->drive_id);
        }

        $category->files()->delete();

        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }
    }

    public function destroyFile(Category $category, CategoryFile $file)
    {
        $this->drive->delete($file->drive_id);
        $file->delete();

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    public function getChildren(Category $category)
    {
        $children = $category->children()->active()->get();

        return response()->json($children);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:categories,id',
            'order.*.order' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            Category::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Orden actualizado correctamente.']);
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active ? 'activada' : 'desactivada';

        return back()->with('success', "Categoría {$status} exitosamente.");
    }

    // -------------------------------------------------------
    // Helper privado: sube múltiples archivos a Google Drive
    // -------------------------------------------------------
    private function uploadFiles(Request $request, Category $category, string $slug): void
    {
        if (! $request->hasFile('archivos')) {
            return;
        }

        $mimesPorTipo = [
            'pdf'    => ['pdf'],
            'doc'    => ['doc', 'docx'],
            'audio'  => ['mp3', 'wav', 'ogg', 'mpeg'],
            'video'  => ['mp4', 'webm', 'mov', 'm4v'],
            'imagen' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        ];
        $maxKbPorTipo = [
            'pdf'   => 102400,  // 100 MB
            'doc'   => 102400,  // 100 MB
            'audio' => 153600,  // 150 MB
            'video' => 307200,  // 300 MB
            'imagen' => 10240,  // 10 MB
        ];

        $ultimoOrden = $category->files()->max('orden') ?? -1;

        foreach ($request->file('archivos') as $file) {
            $extension = strtolower($file->getClientOriginalExtension());

            // Detectar tipo
            $tipo = null;
            foreach ($mimesPorTipo as $t => $exts) {
                if (in_array($extension, $exts, true)) {
                    $tipo = $t;
                    break;
                }
            }
            if (! $tipo) {
                continue;
            }

            // Validar límite por tipo
            $maxKb = $maxKbPorTipo[$tipo] ?? 307200;
            if ($file->getSize() > $maxKb * 1024) {
                continue;
            }

            $original = $file->getClientOriginalName();
            $nombre = $slug.'-'.$tipo.'-'.uniqid().'.'.$extension;

            $driveId = $this->drive->upload($file, $tipo, $nombre);

            CategoryFile::create([
                'category_id' => $category->id,
                'tipo' => $tipo,
                'drive_id' => $driveId,
                'nombre_original' => $original,
                'orden' => ++$ultimoOrden,
            ]);
        }
    }

    /**
     * Árbol anidado (no aplanado) para el selector de categoría padre — permite
     * desglosar de a un nivel por vez en vez de escanear un <select> larguísimo.
     * $exclude quita esa categoría Y toda su rama (no se puede mover un registro
     * dentro de sí mismo ni de uno de sus propios hijos).
     *
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function buildParentTree(?int $exclude = null): \Illuminate\Support\Collection
    {
        $byParent = Category::orderBy('order')->get()->groupBy('parent_id');

        $build = function ($parentId, $depth) use (&$build, $byParent, $exclude) {
            return $byParent->get($parentId, collect())
                ->filter(fn (Category $cat) => $cat->id !== $exclude)
                ->map(function (Category $cat) use ($build, $depth) {
                    $cat->depth = $depth;
                    $cat->setRelation('children', $build($cat->id, $depth + 1));

                    return $cat;
                })
                ->values();
        };

        return $build(null, 0);
    }

    // -------------------------------------------------------
    // Helper privado: guarda imagen en public/img/sisipedia/
    // -------------------------------------------------------
    private function storeImage(\Illuminate\Http\UploadedFile $file, string $slug): string
    {
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = "{$slug}-img.{$extension}";
        $directorio = public_path('img/sisipedia');
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";

        $counter = 1;
        while (file_exists($rutaCompleta)) {
            $nombreArchivo = "{$slug}-img-{$counter}.{$extension}";
            $rutaCompleta = "{$directorio}/{$nombreArchivo}";
            $counter++;
        }

        if (! file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $file->move($directorio, $nombreArchivo);

        return "img/sisipedia/{$nombreArchivo}";
    }
}
