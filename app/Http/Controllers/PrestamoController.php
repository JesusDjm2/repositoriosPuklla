<?php

namespace App\Http\Controllers;

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
        // Solo se precarga el libro ya seleccionado (repoblado tras un error de validación);
        // el resto de opciones las trae Select2 vía AJAX (evita cargar ~9,000 libros).
        $libroSeleccionado = old('libro_fisico_id')
            ? LibroFisico::with('categoriasFisicas')->find(old('libro_fisico_id'))
            : null;
        $hayLibros = LibroFisico::query()->exists();

        return view('prestamos.create', compact('libroSeleccionado', 'hayLibros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'libro_fisico_id' => 'required|exists:libros_fisicos,id',
            'nombre_prestatario' => 'required|string|max:255',
            'dni_prestatario' => 'required|string|max:20',
            'contacto_prestatario' => 'nullable|string|max:255',
            'fecha_prestamo' => 'required|date',
            'fecha_limite' => 'required|date|after_or_equal:fecha_prestamo',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($request) {
            $libro = LibroFisico::lockForUpdate()->findOrFail($request->input('libro_fisico_id'));
            $prestados = $libro->prestamos()->activos()->lockForUpdate()->count();

            if ($prestados >= $libro->cantidad_ejemplares) {
                throw ValidationException::withMessages([
                    'libro_fisico_id' => 'No hay ejemplares disponibles de "'.$libro->nombre.'" para prestar.',
                ]);
            }

            Prestamo::create($request->only([
                'libro_fisico_id',
                'nombre_prestatario',
                'dni_prestatario',
                'contacto_prestatario',
                'fecha_prestamo',
                'fecha_limite',
                'observaciones',
            ]));
        });

        return redirect()->route('biblioteca-fisica.prestamos.index')
            ->with('success', 'Préstamo registrado correctamente.');
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
