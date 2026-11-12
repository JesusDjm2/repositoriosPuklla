<?php

namespace App\Http\Controllers;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayTemaComentario;
use App\Services\AntiSpamComentarios;
use App\Services\Contador;
use App\Services\KawsayPortada;
use App\Services\KawsayTaxonomia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KawsayAreaController extends Controller
{
    public function area(string $area)
    {
        $areaModel = KawsayArea::where('slug', $area)->firstOrFail();

        $temas = $areaModel->temas()->withCount('archivos')->orderBy('orden')->get();

        // Por tema: cuántos videos / fotos / documentos tiene (una sola consulta),
        // para mostrarlo en cada tarjeta de la página del área.
        $formatosPorTema = KawsayArchivo::query()
            ->whereIn('kawsay_tema_id', $temas->pluck('id'))
            ->selectRaw("kawsay_tema_id, CASE WHEN tipo = 'video' THEN 'video' WHEN tipo = 'audio' THEN 'audio' WHEN tipo = 'imagen' THEN 'foto' ELSE 'documento' END AS grupo, COUNT(*) AS total")
            ->groupBy('kawsay_tema_id', 'grupo')
            ->get()
            ->groupBy('kawsay_tema_id')
            ->map(fn ($filas) => $filas->pluck('total', 'grupo'));

        return view('kawsay.areas.show', [
            'areaSlug' => $area,
            'area' => $areaModel,
            'temas' => $temas,
            'formatosPorTema' => $formatosPorTema,
        ]);
    }

    public function tema(Request $request, KawsayPortada $portada, string $area, string $temaSlug)
    {
        $areaModel = KawsayArea::where('slug', $area)->firstOrFail();

        $tema = $areaModel->temas()->where('slug', $temaSlug)->firstOrFail();
        $tema->load(['comentarios']);

        // Visitas del tema (una vez por sesión; no cuenta robots).
        Contador::sumar($tema, 'visitas', 'kawsay_temas_vistos', $request);

        $conteos = $tema->archivos()
            ->selectRaw('tipo_contenido, count(*) as total')
            ->groupBy('tipo_contenido')
            ->pluck('total', 'tipo_contenido');

        $fotos = $tema->archivos()
            ->where('tipo_contenido', 'galeria')
            ->orderBy('id')
            ->get(['id', 'titulo', 'drive_id']);

        return view('kawsay.temas.show', [
            'fotos' => $fotos,
            // Libros y videos del catálogo asignados a este tema (desde el panel).
            'catalogo' => $portada->catalogoDeTema($tema),
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

        $volver = route('kawsay.tema', [$area, $temaSlug]).'#comentarios';

        $validador = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:80',
            'comentario' => 'required|string|min:5|max:1000',
        ], [
            'nombre.required' => 'Escribe tu nombre.',
            'nombre.min' => 'Tu nombre es demasiado corto.',
            'comentario.required' => 'Escribe tu comentario.',
            'comentario.min' => 'El comentario es demasiado corto (mínimo 5 caracteres).',
            'comentario.max' => 'El comentario no puede pasar de 1000 caracteres.',
        ]);

        if ($validador->fails()) {
            return redirect($volver)->withErrors($validador, 'comentario')->withInput();
        }

        $validated = $validador->validated();

        $nombre = AntiSpamComentarios::limpiar($validated['nombre']);
        $comentario = AntiSpamComentarios::limpiar($validated['comentario'], true);

        if (mb_strlen($nombre) < 2 || mb_strlen($comentario) < 5) {
            return redirect($volver)->withInput()->with('comentario_error', 'Revisa tu nombre y tu comentario.');
        }

        $antiSpam = app(AntiSpamComentarios::class);
        $resultado = $antiSpam->evaluar($request, $tema->id, $nombre, $comentario);

        if (! empty($resultado['bot'])) {
            // Bot: se responde como si hubiera funcionado, pero no se guarda nada.
            return redirect($volver)->with('comentario_ok', '¡Gracias por tu comentario!');
        }

        if (! $resultado['ok']) {
            return redirect($volver)->withInput()->with('comentario_error', $resultado['error']);
        }

        $antiSpam->registrarPublicacion($request);

        KawsayTemaComentario::create([
            'kawsay_tema_id' => $tema->id,
            'nombre' => $nombre,
            'comentario' => $comentario,
        ]);

        return redirect($volver)->with('comentario_ok', '¡Gracias! Tu comentario ya está publicado.');
    }
}
