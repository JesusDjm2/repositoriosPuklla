<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\User;
use App\Services\ProyectoEstadisticasService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /** Dashboard principal del rol admin: visión general de proyectos, admins y registros. */
    public function principal(ProyectoEstadisticasService $stats)
    {
        $proyectos = Proyecto::activos()->ordenados()->get();
        $conteos = $stats->paraTodos($proyectos->pluck('slug')->all());
        $totalUsuarios = User::count();
        $totalProyectos = $proyectos->count();

        return view('admin.principal', compact('proyectos', 'conteos', 'totalUsuarios', 'totalProyectos'));
    }

    public function index()
    {
        $admins = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'alumno');
        })->get();
        $alumnos = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumno');
        })->get();
        return view('auth.admins.index', compact('admins', 'alumnos'));
    }
    public function alumnos()
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumno');
        })->get();
        return view('auth.admins.alumnos', compact('admins'));
    }
    public function bibliotecario(ProyectoEstadisticasService $stats)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'biblioteca');
        })->get();
        $conteos = $stats->paraRol('biblioteca');

        $statsData = [
            'secciones' => \App\Models\Seccion::count(),
            'categorias' => \App\Models\Categoria::count(),
            'libros' => \App\Models\Libro::count(),
            'libros_fisicos' => \App\Models\LibroFisico::count(),
            'categorias_fisicas' => \App\Models\CategoriaFisica::count(),
            'prestamos_total' => \App\Models\Prestamo::count(),
            'prestamos_activos' => \App\Models\Prestamo::activos()->count(),
            'prestamos_atrasados' => \App\Models\Prestamo::atrasados()->count(),
            'materiales' => \App\Models\MaterialDidactico::count(),
            'material_categorias' => \App\Models\CategoriaMaterialDidactico::count(),
            'taqe_trabajos' => \App\Models\TaqeTrabajo::count(),
            'taqe_categorias' => \App\Models\TaqeCategoria::count(),
            'videos' => \App\Models\Video::count(),
            'video_categorias' => \App\Models\VideoCategoria::count(),
            'canciones' => \App\Models\Canciones::count(),
            'canciones_categorias' => \App\Models\CancionesCategoria::count(),
        ];

        return view('admin.bibliotecario', compact('admins', 'conteos', 'statsData'));
    }
    public function kawsay(ProyectoEstadisticasService $stats)
    {
        $conteos = $stats->paraRol('kawsay');
        return view('admin.kawsay', compact('conteos'));
    }
    public function videos(ProyectoEstadisticasService $stats)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'videos');
        })->get();
        $conteos = $stats->paraRol('videos');
        return view('admin.videos', compact('admins', 'conteos'));
    }
    public function canciones(ProyectoEstadisticasService $stats)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'audios');
        })->get();
        $conteos = $stats->paraRol('audios');
        return view('admin.canciones', compact('admins', 'conteos'));
    }
    /**
     * Acceso histórico a Sisichakunay: redirige al Panel unificado de Radio & Sisichakunay.
     */
    public function sisicha()
    {
        return redirect()->route('adminRadio');
    }
    /** Panel combinado del admin general para Radio Pukllasunchis (sisicha + audios + videos). */
    public function radio(ProyectoEstadisticasService $stats)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['sisicha', 'audios', 'videos']);
        })->get();
        $conteos = $stats->paraProyecto('radio-pukllasunchis');
        return view('admin.radio', compact('admins', 'conteos'));
    }
    public function create()
    {
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.index')->with('success', 'Usuario creado exitosamente.');
    }
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('auth.admins.edit', compact('user', 'roles'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        $user->syncRoles($request->role);

        return redirect()->route('admin.index')->with('success', 'Usuario actualizado exitosamente.');
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
