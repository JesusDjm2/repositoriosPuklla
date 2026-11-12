<?php

namespace App\Http\Controllers;

use App\Models\KawsayVideo;
use App\Models\KawsayVideoCategoria;
use Illuminate\Http\Request;

class KawsayVideoController extends Controller
{
    public function index()
    {
        $videos = KawsayVideo::all();

        return view('kawsay.videos.index', compact('videos'));
    }

    public function create()
    {
        $categorias = KawsayVideoCategoria::all();

        $temasPorArea = $this->temasPorArea();

        return view('kawsay.videos.create', compact('categorias', 'temasPorArea'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:kawsay_videos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'categoria_id' => 'required|exists:kawsay_video_categorias,id',
            'youtube' => 'nullable|string|max:255',
            'drive' => 'nullable|string|max:255',
            'kawsay_tema_id' => 'nullable|exists:kawsay_temas,id',
        ]);

        KawsayVideo::create($request->only('nombre', 'descripcion', 'categoria_id', 'youtube', 'drive') + $this->datosTema($request));

        return redirect()->route('kawsayvideos.index')->with('success', 'Video creado exitosamente.');
    }

    public function show($id)
    {
        $video = KawsayVideo::findOrFail($id);

        return view('kawsay.videos.show', compact('video'));
    }

    public function edit($id)
    {
        $video = KawsayVideo::findOrFail($id);
        $categorias = KawsayVideoCategoria::all();

        $temasPorArea = $this->temasPorArea();

        return view('kawsay.videos.edit', compact('video', 'categorias', 'temasPorArea'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:kawsay_videos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'categoria_id' => 'required|exists:kawsay_video_categorias,id',
            'youtube' => 'nullable|string|max:255',
            'drive' => 'nullable|string|max:255',
            'kawsay_tema_id' => 'nullable|exists:kawsay_temas,id',
        ]);

        $video = KawsayVideo::findOrFail($id);
        $video->update($request->only('nombre', 'descripcion', 'categoria_id', 'youtube', 'drive') + $this->datosTema($request));

        return redirect()->route('kawsayvideos.index')->with('success', 'Video actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $video = KawsayVideo::findOrFail($id);
        $video->delete();

        return redirect()->route('kawsayvideos.index')->with('success', 'Video eliminado exitosamente.');
    }

    /** Áreas con sus temas, para el selector "Tema del repositorio" (opcional). */
    private function temasPorArea()
    {
        return \App\Models\KawsayArea::orderBy('orden')
            ->with(['temas' => fn ($q) => $q->orderBy('orden')->select('id', 'kawsay_area_id', 'titulo')])
            ->get(['id', 'nombre']);
    }

    /** kawsay_tema_id solo si la columna ya existe (en producción falta hasta ejecutar el .sql). */
    private function datosTema(Request $request): array
    {
        if (! \App\Services\Contador::columnaExiste('kawsay_videos', 'kawsay_tema_id')) {
            return [];
        }

        return ['kawsay_tema_id' => $request->input('kawsay_tema_id') ?: null];
    }
}
