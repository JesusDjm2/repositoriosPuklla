<?php

namespace App\Http\Controllers;

use App\Models\KawsayArea;
use App\Models\KawsayTema;
use App\Models\KawsayTemaComentario;
use Illuminate\Http\Request;

/**
 * Gestor de comentarios públicos de Kawsay: todos los comentarios de todos
 * los temas en un solo lugar, con su relación de pertenencia (tema → área)
 * a la vista, para poder moderarlos (buscar, filtrar, borrar) sin tener que
 * entrar tema por tema.
 */
class KawsayComentarioController extends Controller
{
    public function index(Request $request)
    {
        $areas = KawsayArea::orderBy('orden')->get(['id', 'nombre', 'color']);

        $temas = KawsayTema::with('area:id,nombre')
            ->withCount('comentarios')
            ->when($request->filled('area'), fn ($q) => $q->where('kawsay_area_id', $request->area))
            ->orderBy('titulo')
            ->get(['id', 'titulo', 'kawsay_area_id']);

        $query = KawsayTemaComentario::with('tema.area:id,nombre,slug,color');

        if ($request->filled('area')) {
            $query->whereHas('tema', fn ($q) => $q->where('kawsay_area_id', $request->area));
        }
        if ($request->filled('kawsay_tema_id')) {
            $query->where('kawsay_tema_id', $request->kawsay_tema_id);
        }
        if ($request->filled('q')) {
            $texto = trim((string) $request->q);
            $like = '%'.addcslashes($texto, '%_\\').'%';
            $query->where(fn ($q) => $q->where('nombre', 'like', $like)->orWhere('comentario', 'like', $like));
        }

        $comentarios = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => KawsayTemaComentario::count(),
            'hoy' => KawsayTemaComentario::whereDate('created_at', today())->count(),
            'semana' => KawsayTemaComentario::where('created_at', '>=', now()->subDays(7))->count(),
            'temas' => KawsayTemaComentario::distinct('kawsay_tema_id')->count('kawsay_tema_id'),
        ];

        return view('kawsay.comentarios.index', compact('comentarios', 'areas', 'temas', 'stats'));
    }

    public function destroy(KawsayTemaComentario $comentario)
    {
        $comentario->delete();

        return redirect()->back()->with('success', 'Comentario eliminado.');
    }

    public function destroyMasivo(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), fn ($id) => is_numeric($id));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No seleccionaste ningún comentario.');
        }

        $n = KawsayTemaComentario::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', $n === 1 ? '1 comentario eliminado.' : "{$n} comentarios eliminados.");
    }
}
