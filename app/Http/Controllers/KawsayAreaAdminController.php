<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class KawsayAreaAdminController extends Controller
{
    public function index()
    {
        $areas = KawsayArea::with(['temas' => fn ($q) => $q->withCount('comentarios')])
            ->withCount('temas')
            ->orderBy('orden')
            ->get();

        // Archivos por tema y por tipo de contenido: [tema_id => [tipo => total]]
        $conteos = KawsayArchivo::selectRaw('kawsay_tema_id, tipo_contenido, count(*) as total')
            ->groupBy('kawsay_tema_id', 'tipo_contenido')
            ->get()
            ->groupBy('kawsay_tema_id')
            ->map(fn ($filas) => $filas->pluck('total', 'tipo_contenido')->all())
            ->all();

        $tiposContenido = KawsayTaxonomia::tiposContenido();

        return view('kawsay.areas_admin.index', compact('areas', 'conteos', 'tiposContenido'));
    }

    public function create()
    {
        return view('kawsay.areas_admin.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validarDatos($request);

        $validated['slug'] = $this->slugUnico($validated['nombre']);

        if (empty($validated['orden'])) {
            $validated['orden'] = (int) KawsayArea::max('orden') + 1;
        }

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $this->storeImage($request->file('imagen'), $validated['slug']);
        }

        KawsayArea::create($validated);

        return redirect()->route('kawsayareas.index')->with('success', 'Área creada correctamente.');
    }

    public function edit(KawsayArea $area)
    {
        return view('kawsay.areas_admin.edit', compact('area'));
    }

    public function update(Request $request, KawsayArea $area)
    {
        $validated = $this->validarDatos($request);

        if (Str::slug($validated['nombre']) !== $area->slug) {
            $validated['slug'] = $this->slugUnico($validated['nombre'], $area->id);
        }

        if ($request->boolean('remove_imagen')) {
            if ($area->imagen && file_exists(public_path($area->imagen))) {
                unlink(public_path($area->imagen));
            }
            $validated['imagen'] = null;
        }
        if ($request->hasFile('imagen')) {
            if ($area->imagen && file_exists(public_path($area->imagen))) {
                unlink(public_path($area->imagen));
            }
            $validated['imagen'] = $this->storeImage($request->file('imagen'), $validated['slug'] ?? $area->slug);
        }

        $area->update($validated);

        return redirect()->route('kawsayareas.index')->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(KawsayArea $area)
    {
        if ($area->temas()->exists()) {
            return back()->withErrors(['area' => 'No se puede eliminar: todavía tiene temas asociados. Muévelos o elimínalos primero.']);
        }

        if ($area->imagen && file_exists(public_path($area->imagen))) {
            unlink(public_path($area->imagen));
        }

        $area->delete();

        return redirect()->route('kawsayareas.index')->with('success', 'Área eliminada.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:20|regex:/^#[0-9a-fA-F]{6}$/',
            'descripcion' => 'nullable|string|max:2000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'orden' => 'nullable|integer',
        ]);
    }

    private function slugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $i = 1;

        while (
            KawsayArea::where('slug', $slug)
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function storeImage(UploadedFile $file, string $slug): string
    {
        $carpeta = 'img/kawsay/areas';
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = "{$slug}.{$extension}";
        $directorio = public_path($carpeta);
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";

        $counter = 1;
        while (file_exists($rutaCompleta)) {
            $nombreArchivo = "{$slug}-{$counter}.{$extension}";
            $rutaCompleta = "{$directorio}/{$nombreArchivo}";
            $counter++;
        }

        if (! file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $file->move($directorio, $nombreArchivo);

        return "{$carpeta}/{$nombreArchivo}";
    }
}
