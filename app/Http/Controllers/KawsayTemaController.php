<?php

namespace App\Http\Controllers;

use App\Models\KawsayArea;
use App\Models\KawsayTema;
use App\Models\KawsayTemaComentario;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class KawsayTemaController extends Controller
{
    public function index(Request $request)
    {
        $areas = KawsayArea::orderBy('orden')->get();

        $temas = KawsayTema::query()
            ->with('area')
            ->when($request->filled('area'), fn ($q) => $q->where('kawsay_area_id', $request->area))
            ->withCount(['archivos', 'comentarios'])
            ->orderBy('kawsay_area_id')
            ->orderBy('orden')
            ->get();

        return view('kawsay.temas.index', compact('temas', 'areas'));
    }

    public function create()
    {
        $areas = KawsayArea::orderBy('orden')->get();

        return view('kawsay.temas.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $this->validarDatos($request);

        $validated['slug'] = $this->slugUnico($validated['kawsay_area_id'], $validated['titulo']);

        if (empty($validated['orden'])) {
            $validated['orden'] = KawsayTema::where('kawsay_area_id', $validated['kawsay_area_id'])->max('orden') + 1;
        }

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $this->storeImage($request->file('imagen'), $validated['slug']);
        }

        KawsayTema::create($validated);

        return redirect()->route('kawsaytemas.index')->with('success', 'Tema creado correctamente.');
    }

    public function edit(KawsayTema $tema)
    {
        $areas = KawsayArea::orderBy('orden')->get();
        $tiposContenido = KawsayTaxonomia::tiposContenido();
        $tema->load(['comentarios', 'archivos']);
        $archivosPorTipo = $tema->archivos->groupBy('tipo_contenido');

        return view('kawsay.temas.edit', compact('tema', 'areas', 'tiposContenido', 'archivosPorTipo'));
    }

    public function update(Request $request, KawsayTema $tema)
    {
        $validated = $this->validarDatos($request);

        if ($tema->kawsay_area_id !== (int) $validated['kawsay_area_id'] || Str::slug($validated['titulo']) !== $tema->slug) {
            $validated['slug'] = $this->slugUnico($validated['kawsay_area_id'], $validated['titulo'], $tema->id);
        }

        if ($request->boolean('remove_imagen')) {
            if ($tema->imagen && file_exists(public_path($tema->imagen))) {
                unlink(public_path($tema->imagen));
            }
            $validated['imagen'] = null;
        }
        if ($request->hasFile('imagen')) {
            if ($tema->imagen && file_exists(public_path($tema->imagen))) {
                unlink(public_path($tema->imagen));
            }
            $validated['imagen'] = $this->storeImage($request->file('imagen'), $validated['slug'] ?? $tema->slug);
        }

        $tema->update($validated);

        return redirect()->route('kawsaytemas.edit', $tema)->with('success', 'Tema actualizado correctamente.');
    }

    public function destroy(KawsayTema $tema)
    {
        if ($tema->imagen && file_exists(public_path($tema->imagen))) {
            unlink(public_path($tema->imagen));
        }

        $tema->delete();

        return redirect()->route('kawsaytemas.index')->with('success', 'Tema eliminado.');
    }

    public function destroyComentario(KawsayTema $tema, KawsayTemaComentario $comentario)
    {
        if ($comentario->kawsay_tema_id !== $tema->id) {
            abort(404);
        }

        $comentario->delete();

        return redirect()->route('kawsaytemas.edit', $tema)->with('success', 'Comentario eliminado.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'kawsay_area_id' => 'required|exists:kawsay_areas,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'orden' => 'nullable|integer',
        ]);
    }

    private function slugUnico(int $areaId, string $titulo, ?int $ignorarId = null): string
    {
        $base = Str::slug($titulo);
        $slug = $base;
        $i = 1;

        while (
            KawsayTema::where('kawsay_area_id', $areaId)->where('slug', $slug)
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function storeImage(UploadedFile $file, string $slug, string $carpeta = 'img/kawsay/temas'): string
    {
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
