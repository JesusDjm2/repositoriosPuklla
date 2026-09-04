<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFisica;
use App\Models\LibroFisico;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestamo::query()
            ->with('libroFisico')
            ->latest('fecha_prestamo');

        if ($request->filled('q')) {
            $term = '%'.addcslashes($request->input('q'), '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre_prestatario', 'like', $term)
                    ->orWhere('dni_prestatario', 'like', $term);
            });
        }

        if ($request->filled('libro_fisico_id')) {
            $query->where('libro_fisico_id', (int) $request->input('libro_fisico_id'));
        }

        if ($request->filled('estado')) {
            match ($request->input('estado')) {
                'devuelto' => $query->whereNotNull('fecha_devolucion'),
                'atrasado' => $query->whereNull('fecha_devolucion')->whereDate('fecha_limite', '<', today()),
                'prestado' => $query->whereNull('fecha_devolucion')->whereDate('fecha_limite', '>=', today()),
                default => null,
            };
        }

        $prestamos = $query->paginate(25)->withQueryString();

        // Solo se precarga el libro ya seleccionado en el filtro (si hay uno);
        // el resto de opciones las trae Select2 vía AJAX (evita cargar ~9,000 libros).
        $libroFiltroSeleccionado = $request->filled('libro_fisico_id')
            ? LibroFisico::find($request->input('libro_fisico_id'))
            : null;

        $totalEjemplares = (int) LibroFisico::sum('cantidad_ejemplares');
        $prestadosActualmente = Prestamo::activos()->count();
        $atrasados = Prestamo::atrasados()->count();
        $disponibles = max(0, $totalEjemplares - $prestadosActualmente);

        return view('prestamos.index', compact(
            'prestamos',
            'libroFiltroSeleccionado',
            'totalEjemplares',
            'prestadosActualmente',
            'atrasados',
            'disponibles'
        ));
    }

    public function create()
    {
        // Repuebla la selección de libros tras un error de validación (vuelve del
        // servidor con los ids ya elegidos); el resto de opciones las trae el
        // buscador propio vía AJAX (evita cargar ~9,000 libros).
        $idsSeleccionados = collect(old('libro_fisico_id', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $librosSeleccionados = $idsSeleccionados->isEmpty()
            ? collect()
            : LibroFisico::with('categoriasFisicas')
                ->whereIn('id', $idsSeleccionados)
                ->get()
                ->sortBy(fn (LibroFisico $libro) => $idsSeleccionados->search($libro->id))
                ->values()
                ->map(fn (LibroFisico $libro) => [
                    'id' => $libro->id,
                    'nombre' => $libro->nombre,
                    'autor' => $libro->autor,
                    'codigo' => $libro->codigo,
                    'ejemplares_disponibles' => $libro->ejemplares_disponibles,
                    'ubicacion' => LibroFisico::nombreParaUbicacion($libro->ubicacion),
                    'categorias' => $libro->categoriasFisicas->map(fn (CategoriaFisica $c) => [
                        'nombre' => $c->nombre,
                        'color' => CategoriaFisica::colorForCodigo($c->codigo),
                    ])->values(),
                    'tipo_material' => $libro->tipo_material ? [
                        'nombre' => CategoriaFisica::nombreParaTipoMaterial($libro->tipo_material),
                        'color' => CategoriaFisica::colorForTipoMaterial($libro->tipo_material),
                    ] : null,
                ]);

        $hayLibros = LibroFisico::query()->exists();

        return view('prestamos.create', compact('librosSeleccionados', 'hayLibros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'libro_fisico_id' => 'required|array|min:1',
            'libro_fisico_id.*' => 'distinct|exists:libros_fisicos,id',
            'nombre_prestatario' => 'required|string|max:255',
            'dni_prestatario' => 'required|string|max:20',
            'contacto_prestatario' => 'nullable|string|max:255',
            'fecha_prestamo' => 'required|date',
            'fecha_limite' => 'required|date|after_or_equal:fecha_prestamo',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $cantidad = DB::transaction(function () use ($request) {
            $librosIds = $request->input('libro_fisico_id');

            foreach ($librosIds as $libroId) {
                $libro = LibroFisico::lockForUpdate()->findOrFail($libroId);
                $prestados = $libro->prestamos()->activos()->lockForUpdate()->count();

                if ($prestados >= $libro->cantidad_ejemplares) {
                    throw ValidationException::withMessages([
                        'libro_fisico_id' => 'No hay ejemplares disponibles de "'.$libro->nombre.'" para prestar.',
                    ]);
                }

                Prestamo::create([
                    'libro_fisico_id' => $libro->id,
                    'nombre_prestatario' => $request->input('nombre_prestatario'),
                    'dni_prestatario' => $request->input('dni_prestatario'),
                    'contacto_prestatario' => $request->input('contacto_prestatario'),
                    'fecha_prestamo' => $request->input('fecha_prestamo'),
                    'fecha_limite' => $request->input('fecha_limite'),
                    'observaciones' => $request->input('observaciones'),
                ]);
            }

            return count($librosIds);
        });

        return redirect()->route('biblioteca-fisica.prestamos.index')
            ->with('success', $cantidad === 1
                ? 'Préstamo registrado correctamente.'
                : "Se registraron {$cantidad} préstamos correctamente.");
    }

    public function edit(Prestamo $prestamo)
    {
        $prestamo->load('libroFisico');

        return view('prestamos.edit', compact('prestamo'));
    }

    public function update(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'nombre_prestatario' => 'required|string|max:255',
            'dni_prestatario' => 'required|string|max:20',
            'contacto_prestatario' => 'nullable|string|max:255',
            'fecha_prestamo' => 'required|date',
            'fecha_limite' => 'required|date|after_or_equal:fecha_prestamo',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $prestamo->update($request->only([
            'nombre_prestatario',
            'dni_prestatario',
            'contacto_prestatario',
            'fecha_prestamo',
            'fecha_limite',
            'observaciones',
        ]));

        return redirect()->route('biblioteca-fisica.prestamos.index')
            ->with('success', 'Préstamo actualizado correctamente.');
    }

    public function destroy(Prestamo $prestamo)
    {
        $prestamo->delete();

        return redirect()->route('biblioteca-fisica.prestamos.index')
            ->with('success', 'Préstamo eliminado correctamente.');
    }

    public function devolver(Request $request, Prestamo $prestamo)
    {
        if ($prestamo->fecha_devolucion !== null) {
            return back()->with('error', 'Este préstamo ya fue registrado como devuelto.');
        }

        $prestamo->update(['fecha_devolucion' => today()]);

        return redirect()->route('biblioteca-fisica.prestamos.index')
            ->with('success', 'Devolución registrada correctamente.');
    }
}
