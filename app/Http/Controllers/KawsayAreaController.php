<?php

namespace App\Http\Controllers;

use App\Models\KawsayArea;
use App\Models\KawsayTemaComentario;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;

class KawsayAreaController extends Controller
{
    public function area(string $area)
    {
        $areaModel = KawsayArea::where('slug', $area)->firstOrFail();

        $temas = $areaModel->temas()->withCount('archivos')->get();

        return view('kawsay.areas.show', [
            'areaSlug' => $area,
            'area' => $areaModel,
            'temas' => $temas,
        ]);
    }

    public function tema(string $area, string $temaSlug)
    {
        $areaModel = KawsayArea::where('slug', $area)->firstOrFail();

        $tema = $areaModel->temas()->where('slug', $temaSlug)->firstOrFail();
        $tema->load(['imagenes', 'comentarios']);

        $conteos = $tema->archivos()
            ->selectRaw('tipo_contenido, count(*) as total')
            ->groupBy('tipo_contenido')
            ->pluck('total', 'tipo_contenido');

        return view('kawsay.temas.show', [
            'areaSlug' => $area,
            'area' => $areaModel,
            'temaSlug' => $temaSlug,
            'tema' => $tema,
            'tiposContenido' => KawsayTaxonomia::tiposContenido(),
            'conteos' => $conteos,
        ]);
    }

    public function contenido(string $area, string $temaSlug, string $tipo)
    {
        $areaModel = KawsayArea::where('slug', $area)->firstOrFail();

        $tema = $areaModel->temas()->where('slug', $temaSlug)->firstOrFail();

        $tipoInfo = KawsayTaxonomia::tipoContenido($tipo);
        abort_if(! $tipoInfo, 404);

        $archivos = $tema->archivos()->where('tipo_contenido', $tipo)->latest()->get();

        return view('kawsay.temas.contenido', [
            'areaSlug' => $area,
            'area' => $areaModel,
            'temaSlug' => $temaSlug,
            'tema' => $tema,
            'tipoSlug' => $tipo,
            'tipo' => $tipoInfo,
            'archivos' => $archivos,
        ]);
    }

    public function comentarioStore(Request $request, string $area, string $temaSlug)
    {
        $areaModel = KawsayArea::where('slug', $area)->firstOrFail();
        $tema = $areaModel->temas()->where('slug', $temaSlug)->firstOrFail();

        $validated = $request->validate([
            'nombre' => 'required|string|max:80',
            'comentario' => 'required|string|max:1000',
        ]);

        KawsayTemaComentario::create([
            'kawsay_tema_id' => $tema->id,
            'nombre' => $validated['nombre'],
            'comentario' => $validated['comentario'],
        ]);

        return redirect(route('kawsay.tema', [$area, $temaSlug]).'#comentarios')->with('success', 'Comentario publicado.');
    }
}
