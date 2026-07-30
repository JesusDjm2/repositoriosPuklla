<?php

namespace App\Http\Controllers;

use App\Models\KawsayVideo;
use App\Models\KawsayVideoCategoria;
use Illuminate\Http\Request;

class KawsayVideoCategoriaController extends Controller
{
    public function index()
    {
        $videoCategorias = KawsayVideoCategoria::all();

        return view('kawsay.videos.categorias.index', compact('videoCategorias'));
    }

    public function lista()
    {
        $categorias = KawsayVideoCategoria::withCount('videos')
            ->orderBy('nombre', 'asc')
            ->get();

        $videos = KawsayVideo::orderBy('created_at', 'desc')->get();

        return view('kawsay.videos.lista', compact('videos', 'categorias'));
    }

    public function create()
    {
        return view('kawsay.videos.categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:kawsay_video_categorias,nombre',
            'url' => 'required|string|unique:kawsay_video_categorias,url',
        ]);

        KawsayVideoCategoria::create($request->all());

        return redirect()->route('kawsayvideoscat.index')->with('success', 'Categoría de video creada correctamente!');
    }

    public function show($url)
    {
        $categorias = KawsayVideoCategoria::withCount('videos')->orderBy('nombre')->get();
        $categoria = KawsayVideoCategoria::where('url', $url)->firstOrFail();
        $videos = $categoria->videos()->orderBy('created_at', 'desc')->get();

        return view('kawsay.videos.categorias.show', compact('categorias', 'categoria', 'videos'));
    }

    public function edit($id)
    {
        $videoCategoria = KawsayVideoCategoria::findOrFail($id);

        return view('kawsay.videos.categorias.edit', compact('videoCategoria'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|unique:kawsay_video_categorias,nombre,' . $id,
            'url' => 'required|string|unique:kawsay_video_categorias,url,' . $id,
        ]);

        $videoCategoria = KawsayVideoCategoria::findOrFail($id);
        $videoCategoria->update($request->all());

        return redirect()->route('kawsayvideoscat.index')->with('success', 'Categoría de video actualizada correctamente');
    }

    public function destroy($id)
    {
        $videoCategoria = KawsayVideoCategoria::findOrFail($id);
        $videoCategoria->delete();

        return redirect()->route('kawsayvideoscat.index')->with('success', 'Categoría de video eliminada correctamente');
    }
}
