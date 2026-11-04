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
        $categories = Category::with('parent')
            ->orderBy('parent_id')
            ->orderBy('order');
        $tree = Category::with([
            'children.children.children.children',
            'children.files',
            'children.children.files',
            'children.children.children.files',
            'children.children.children.children.files',
            'files',
        ])->whereNull('parent_id')->orderBy('order')->get();

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

        return view('sisichakuna.categorias.index', compact('categories', 'tree', 'aportCounts', 'fileTypeCounts'));
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

        $categories = $this->buildFlatList();
        $categoriesList = $categories->keyBy('id');

        $callback = function () use ($categories, $categoriesList) {
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
                    $category->numbering,
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

    public function publicIndex()
    {
        $tree = Category::with([
            'children.children.children.children',
            'children.files',
            'children.children.files',
            'children.children.children.files',
            'children.children.children.children.files',
        ])
            ->withCount('aportaciones')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

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

        $tree = Category::with([
            'children.children.children.children',
            'children.files',
            'children.children.files',
            'children.children.children.files',
            'children.children.children.children.files',
        ])
            ->withCount('aportaciones')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $aportacionesSueltas = Aportacion::with('archivos')
            ->whereNull('category_id')
            ->where('is_approved', true)
            ->latest()
            ->limit(20)
            ->get();

        $buscarInicial = trim((string) $request->get('buscar', ''));

        return view('sisichakuna.show', compact('tree', 'aportacionesSueltas', 'archivoCounts', 'buscarInicial'));
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

    // -------------------------------------------------------
    // Helper privado: lista plana de categorías en orden árbol
    // -------------------------------------------------------
    private function buildFlatList(?int $exclude = null): \Illuminate\Support\Collection
    {
        $all = Category::orderBy('parent_id')->orderBy('order')->get()->keyBy('id');

        $flat = collect();

        $walk = function ($parentId, $depth) use (&$walk, &$flat, $all, $exclude) {
            foreach ($all->where('parent_id', $parentId)->sortBy('order') as $cat) {
                if ($cat->id === $exclude) {
                    continue;
                }
                $cat->depth = $depth;
                $flat->push($cat);
                $walk($cat->id, $depth + 1);
            }
        };

        $walk(null, 0);

        return $flat;
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
