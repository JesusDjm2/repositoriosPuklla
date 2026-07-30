<?php

namespace App\Http\Controllers;

use App\Models\KawsayCategoria;
use App\Models\KawsayLibro;
use Illuminate\Http\Request;

class KawsayLibroController extends Controller
{
    public function index()
    {
        $libros = KawsayLibro::with('categorias')->get();

        return view('kawsay.libros.index', compact('libros'));
    }

    public function libros()
    {
        $libros = KawsayLibro::orderBy('created_at', 'desc')->get();
        $categorias = KawsayCategoria::withCount('libros')->orderBy('nombre')->get();

        return view('kawsay.libros.lista', compact('libros', 'categorias'));
    }

    public function create($categoriaId = null)
    {
        $categorias = KawsayCategoria::all();
        $categoriaSeleccionada = $categoriaId ? [$categoriaId] : [];

        return view('kawsay.libros.create', compact('categorias', 'categoriaSeleccionada'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'identificador' => 'required|string|max:255|unique:kawsay_libros,identificador',
            'categorias' => 'required|array',
            'categorias.*' => 'exists:kawsay_categorias,id',
        ]);

        $libro = KawsayLibro::create($request->only('nombre', 'autor', 'identificador'));
        $libro->categorias()->attach($request->categorias);

        return redirect()->route('kawsaylibros.index')->with('success', 'Libro creado exitosamente.');
    }

    public function show($id)
    {
        $libro = KawsayLibro::findOrFail($id);

        return view('kawsay.libros.show', compact('libro'));
    }

    public function edit($id)
    {
        $libro = KawsayLibro::findOrFail($id);
        $categorias = KawsayCategoria::all();
        $libroCategorias = $libro->categorias->pluck('id')->toArray();

        return view('kawsay.libros.edit', compact('libro', 'categorias', 'libroCategorias'));
    }

    public function update(Request $request, $id)
    {
        $libro = KawsayLibro::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'identificador' => 'required|string|max:255|unique:kawsay_libros,identificador,' . $id,
            'categorias' => 'array',
            'categorias.*' => 'exists:kawsay_categorias,id',
        ]);

        $libro->update($request->only('nombre', 'autor', 'identificador'));

        if ($request->has('categorias')) {
            $libro->categorias()->sync($request->input('categorias'));
        } else {
            $libro->categorias()->detach();
        }

        return redirect()->route('kawsaylibros.index')->with('success', 'Libro actualizado correctamente');
    }

    public function destroy($id)
    {
        $libro = KawsayLibro::findOrFail($id);
        $libro->delete();

        return redirect()->route('kawsaylibros.index')->with('success', 'Libro eliminado correctamente');
    }
}
