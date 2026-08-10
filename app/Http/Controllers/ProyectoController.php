<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::with('roles')->ordenados()->get();

        return view('admin.proyectos.index', compact('proyectos'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.proyectos.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->storeLogo($request->file('logo'), $validated['slug']);
        }

        $proyecto = Proyecto::create($validated);
        $proyecto->roles()->sync($request->input('roles', []));

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado correctamente.');
    }

    public function edit(Proyecto $proyecto)
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.proyectos.edit', compact('proyecto', 'roles'));
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $validated = $this->validated($request, $proyecto->id);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->storeLogo($request->file('logo'), $validated['slug']);
        }

        $proyecto->update($validated);
        $proyecto->roles()->sync($request->input('roles', []));

        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Proyecto $proyecto)
    {
        $proyecto->delete();

        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado correctamente.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:proyectos,slug' . ($ignoreId ? ",{$ignoreId}" : ''),
            'descripcion' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'orden' => 'nullable|integer|min:0',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        return [
            'nombre' => $request->input('nombre'),
            'slug' => $request->input('slug'),
            'descripcion' => $request->input('descripcion'),
            'color' => $request->input('color'),
            'orden' => (int) $request->input('orden', 0),
            'activo' => $request->boolean('activo'),
        ];
    }

    private function storeLogo(UploadedFile $file, string $slug): string
    {
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = "{$slug}.{$extension}";
        $directorio = public_path('img/proyectos');
        $rutaCompleta = "{$directorio}/{$nombreArchivo}";

        $counter = 1;
        while (file_exists($rutaCompleta)) {
            $nombreArchivo = "{$slug}-{$counter}.{$extension}";
            $rutaCompleta = "{$directorio}/{$nombreArchivo}";
            $counter++;
        }

        if (! file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $file->move($directorio, $nombreArchivo);

        return "proyectos/{$nombreArchivo}";
    }
}
