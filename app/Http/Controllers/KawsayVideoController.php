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

        return view('kawsay.videos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:kawsay_videos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'categoria_id' => 'required|exists:kawsay_video_categorias,id',
            'youtube' => 'nullable|string|max:255',
            'drive' => 'nullable|string|max:255',
        ]);

        KawsayVideo::create($request->all());

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

        return view('kawsay.videos.edit', compact('video', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:kawsay_videos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'categoria_id' => 'required|exists:kawsay_video_categorias,id',
            'youtube' => 'nullable|string|max:255',
            'drive' => 'nullable|string|max:255',
        ]);

        $video = KawsayVideo::findOrFail($id);
        $video->update($request->only('nombre', 'descripcion', 'categoria_id', 'youtube', 'drive'));

        return redirect()->route('kawsayvideos.index')->with('success', 'Video actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $video = KawsayVideo::findOrFail($id);
        $video->delete();

        return redirect()->route('kawsayvideos.index')->with('success', 'Video eliminado exitosamente.');
    }
}
