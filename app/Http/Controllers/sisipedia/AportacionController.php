<?php

namespace App\Http\Controllers\sisipedia;

use App\Http\Controllers\Controller;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\AportacionArchivo;
use App\Models\sisipedia\Category;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AportacionController extends Controller
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    public function adminIndex(Request $request)
    {
        $roles = Aportacion::ROLES;
        $categories = Category::orderBy('name')->get(['id', 'name', 'parent_id']);


        $query = Aportacion::with(['category.parent.parent', 'archivos'])
            ->latest();

        if ($request->filled('rol')) {
            $query->where('rol_nombre', $request->rol);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'pendiente') {
                $query->where(function ($sub) {
                    $sub->where('is_approved', 0)->orWhereNull('is_approved');
                });
            } elseif ($request->estado === 'aprobada') {
                $query->where('is_approved', 1);
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_ol',  'like', "%{$q}%")
                    ->orWhere('institucion', 'like', "%{$q}%")
                    ->orWhere('ubicacion',   'like', "%{$q}%");
            });
        }

        /** @var LengthAwarePaginator $aportaciones */
        $aportaciones = $query->paginate(20);
        $aportaciones->withQueryString();

        $totals = Aportacion::selectRaw('rol_nombre, count(*) as total')
            ->groupBy('rol_nombre')
            ->pluck('total', 'rol_nombre');

        $pendientesGeneral = Aportacion::whereNull('category_id')
            ->where('is_approved', false)
            ->count();

        $categoryPickerRows = $this->categoriesForPicker();

        return view('sisichakuna.aportaciones.index', compact(
            'aportaciones', 'roles', 'categories', 'totals', 'pendientesGeneral', 'categoryPickerRows'
        ));
    }

    /**
     * Lista plana ordenada en profundidad primero, con ruta completa para el selector del modal.
     *
     * @return array<int, array{id:int, name:string, depth:int, path_label:string, search_blob:string, is_root:bool}>
     */
    private function categoriesForPicker(): array
    {
        $all = Category::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $byId = $all->keyBy('id');

        $pathFor = function ($cat) use ($byId): string {
            $parts = [];
            $current = $cat;
            $guard = 0;
            while ($current && $guard++ < 64) {
                array_unshift($parts, $current->display_name);
                $pid = $current->parent_id;
                $current = $pid ? $byId->get($pid) : null;
            }

            return implode(' › ', $parts);
        };

        $byParent = $all->groupBy(fn ($c) => $c->parent_id ?? 0);

        $rows = [];
        $walk = function (int $parentKey, int $depth) use (&$walk, &$rows, $byParent, $pathFor): void {
            foreach ($byParent->get($parentKey, collect()) as $cat) {
                $pathLabel = $pathFor($cat);
                $rows[] = [
                    'id' => $cat->id,
                    'name' => $cat->display_name,
                    'depth' => $depth,
                    'path_label' => $pathLabel,
                    'search_blob' => mb_strtolower($pathLabel.' '.$cat->display_name),
                    'is_root' => $depth === 0,
                ];
                $walk((int) $cat->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $rows;
    }

    /** MIME types válidos por tipo de archivo */
    private function mimesPorTipo(): array
    {
        return [
            'imagen' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'pdf'    => ['pdf'],
            'doc'    => ['doc', 'docx'],
            'audio'  => ['mp3', 'wav', 'ogg', 'mpeg'],
            'video'  => ['mp4', 'webm', 'mov', 'm4v'],
        ];
    }

    /** Límites por tipo de archivo (en KB) */
    private function maxKbPorTipo(): array
    {
        return [
            'imagen' => 10240,    // 10 MB
            'pdf'    => 102400,   // 100 MB
            'doc'    => 102400,   // 100 MB
            'audio'  => 153600,   // 150 MB
            'video'  => 307200,   // 300 MB
        ];
    }

    /** Aporte ligado a un registro (categoría); mismos roles que en la portada. */
    public function store(Request $request, Category $category)
    {
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return back()->withErrors(['archivos' => "Un archivo supera el límite permitido ({$maxMb}MB). Usa archivos más pequeños o comprime."])
                ->withInput();
        }

        $rolesCat = implode(',', Aportacion::ROLES_CON_REGISTRO);

        $request->validate([
            'rol_nombre' => "required|in:{$rolesCat}",
            'nombre_ol' => 'required|string|max:255',
            'titulo' => 'nullable|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'lugar_trabajo' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'detalle' => 'nullable|string',
            'archivos' => 'nullable|array',
            'archivos.*' => $this->reglasArchivoMulti(),
        ]);

        $data = $request->only(['rol_nombre', 'nombre_ol', 'titulo', 'institucion', 'lugar_trabajo', 'ubicacion', 'detalle']);
        $data['category_id'] = $category->id;
        $data['is_approved'] = true;

        $prefix = $category->slug.'-'.Str::slug($request->nombre_ol);

        $aportacion = Aportacion::create($data);

        $this->procesarArchivos($request, $aportacion, $prefix);

        return back()->with('success', 'Aportación creada correctamente.');
    }

    /** Aporte desde la portada: sin categoría; queda pendiente hasta que un administrador apruebe. */
    public function storeStandalone(Request $request)
    {
        if (
            (int) $request->server('CONTENT_LENGTH') > 0 &&
            empty($request->all()) &&
            $request->files->count() === 0
        ) {
            $maxMb = (int) ini_get('upload_max_filesize');

            return redirect()->route('index')
                ->withFragment('home-aporte-standalone')
                ->withErrors(['archivos' => "Un archivo supera el límite permitido ({$maxMb}MB)."])
                ->withInput();
        }

        $roles = implode(',', Aportacion::ROLES);

        $validator = Validator::make($request->all(), [
            'rol_nombre' => "required|in:{$roles}",
            'nombre_ol' => 'required|string|max:255',
            'titulo' => 'nullable|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'lugar_trabajo' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'detalle' => 'nullable|string',
            'archivos' => 'nullable|array',
            'archivos.*' => $this->reglasArchivoMulti(),
        ]);

        if ($validator->fails()) {
            return redirect()->route('index')
                ->withFragment('home-aporte-standalone')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['rol_nombre', 'nombre_ol', 'titulo', 'institucion', 'lugar_trabajo', 'ubicacion', 'detalle']);
        $data['is_approved'] = true;
        $data['category_id'] = $this->autoRelateCategory($request->nombre_ol ?? '');

        $prefix = 'general-'.Str::slug($request->nombre_ol).'-'.Str::random(4);

        $aportacion = Aportacion::create($data);

        $this->procesarArchivos($request, $aportacion, $prefix);

        return redirect()->route('index')
            ->withFragment('home-aporte-standalone')
            ->with('aporte_success', 'Tu aporte se ha publicado correctamente. ¡Gracias por contribuir!');
    }

    public function update(Request $request, Aportacion $aportacion)
    {
        $roles = implode(',', Aportacion::ROLES);

        $validated = $request->validate([
            'nombre_ol'   => 'required|string|max:255',
            'rol_nombre'  => "required|in:{$roles}",
            'titulo'      => 'nullable|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'lugar_trabajo' => 'nullable|string|max:255',
            'ubicacion'   => 'nullable|string|max:255',
            'detalle'     => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'is_approved' => 'boolean',
            'archivos' => 'nullable|array',
            'archivos.*' => $this->reglasArchivoMulti(),
        ]);

        // Solo aceptar category_id si está presente
        if (!$request->filled('category_id')) {
            unset($validated['category_id']);
        }

        $aportacion->update($validated);

        if ($request->hasFile('archivos')) {
            $prefix = 'aportacion-'.Str::slug($validated['nombre_ol']).'-'.Str::random(4);
            $this->procesarArchivos($request, $aportacion, $prefix);
        }

        return back()->with('success', 'Aportación actualizada correctamente.');
    }

    public function approve(Request $request, Aportacion $aportacion)
    {
        $categoryId = $aportacion->category_id;

        if ($aportacion->category_id === null) {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
            ]);
            $categoryId = (int) $validated['category_id'];
        }

        $aportacion->update([
            'is_approved' => true,
            'category_id' => $categoryId,
        ]);

        return back()->with('success', 'Aportación aprobada y vinculada al registro elegido.');
    }

    /**
     * Reglas de validación para un archivo individual dentro del array archivos[].
     */
    private function reglasArchivoMulti(): array
    {
        $allMimes = collect($this->mimesPorTipo())->flatten()->implode(',');

        return [
            'file',
            'mimes:' . $allMimes,
            'max:307200',
        ];
    }

    /**
     * Procesa y sube los archivos del request a Google Drive,
     * creando registros en la tabla aportacion_archivos.
     */
    private function procesarArchivos(Request $request, Aportacion $aportacion, string $prefix): void
    {
        if (!$request->hasFile('archivos')) {
            return;
        }

        $mimesPorTipo = $this->mimesPorTipo();
        $maxKbPorTipo = $this->maxKbPorTipo();

        foreach ($request->file('archivos') as $archivo) {
            $extension = strtolower($archivo->getClientOriginalExtension());

            // Detectar tipo por extensión
            $tipo = null;
            foreach ($mimesPorTipo as $t => $exts) {
                if (in_array($extension, $exts, true)) {
                    $tipo = $t;
                    break;
                }
            }

            if (!$tipo) {
                continue;
            }

            // Validar límite por tipo
            $maxKb = $maxKbPorTipo[$tipo] ?? 307200;
            if ($archivo->getSize() > $maxKb * 1024) {
                continue;
            }

            $pesoMb = round($archivo->getSize() / (1024 * 1024), 2);
            $nombreOriginal = $archivo->getClientOriginalName();
            $nombreDrive = "{$prefix}-{$tipo}-{$nombreOriginal}";

            $driveId = $this->drive->upload($archivo, $tipo, $nombreDrive);

            AportacionArchivo::create([
                'aportacion_id' => $aportacion->id,
                'tipo' => $tipo,
                'drive_id' => $driveId,
                'nombre_original' => $nombreOriginal,
                'peso_mb' => $pesoMb,
            ]);
        }
    }

    public function destroy(Category $category, Aportacion $aportacion)
    {
        if ((int) $aportacion->category_id !== (int) $category->id) {
            abort(404);
        }

        return $this->eliminarAportacion($aportacion);
    }

    /** Eliminar aportación sin categoría o desde panel general */
    public function destroyStandalone(Aportacion $aportacion)
    {
        return $this->eliminarAportacion($aportacion);
    }

    private function eliminarAportacion(Aportacion $aportacion)
    {
        foreach ($aportacion->archivos as $archivo) {
            $this->drive->delete($archivo->drive_id);
            $archivo->delete();
        }

        $aportacion->delete();

        return back()->with('success', 'Aportación eliminada correctamente.');
    }

    /**
     * Busca la categoría con mayor similitud al nombre_ol dado.
     * Criterios (en orden):
     *  1. similar_text() normalizado (sin acentos, minúsculas)
     *  2. Boost a 85% si uno contiene al otro
     * Umbral mínimo: 70%
     */
    private function autoRelateCategory(string $titulo): ?int
    {
        if (trim($titulo) === '') {
            return null;
        }

        $normalTitulo = mb_strtolower(trim(Str::ascii($titulo)));
        $bestId       = null;
        $bestScore    = 0.0;

        Category::where('is_active', true)->get(['id', 'name'])->each(function ($cat) use ($normalTitulo, &$bestId, &$bestScore) {
            $normalName = mb_strtolower(trim(Str::ascii($cat->name)));

            similar_text($normalTitulo, $normalName, $percent);

            if (str_contains($normalName, $normalTitulo) || str_contains($normalTitulo, $normalName)) {
                $percent = max($percent, 85.0);
            }

            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestId    = $cat->id;
            }
        });

        return $bestScore >= 70.0 ? $bestId : null;
    }
}
