<?php

namespace App\Http\Controllers;

use App\Models\KawsayCategoria;
use Illuminate\Http\Request;

class KawsayCategoriaController extends Controller
{
    public function index()
    {
        $categorias = KawsayCategoria::withCount('libros')->get();

        return view('kawsay.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('kawsay.categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:kawsay_categorias,nombre',
            'url' => 'required|string|max:255|unique:kawsay_categorias,url',
        ]);

        KawsayCategoria::create($request->all());

        return redirect()->route('kawsaycategorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function show($url)
    {
        $categoria = KawsayCategoria::where('url', $url)->firstOrFail();
        $libros = $categoria->libros;

        return view('kawsay.categorias.show', compact('categoria', 'libros'));
    }

    public function edit($id)
    {
        $categoria = KawsayCategoria::findOrFail($id);

        return view('kawsay.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:kawsay_categorias,nombre,' . $id,
            'url' => 'required|string|max:255|unique:kawsay_categorias,url,' . $id,
        ]);

        $categoria = KawsayCategoria::findOrFail($id);
        $categoria->update($request->all());

        return redirect()->route('kawsaycategorias.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $categoria = KawsayCategoria::findOrFail($id);
        $categoria->delete();

        return redirect()->route('kawsaycategorias.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
