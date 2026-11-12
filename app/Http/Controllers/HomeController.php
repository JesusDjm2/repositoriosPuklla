<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Admin completo manda sobre cualquier otro rol que también tenga.
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.principal');
        }

        if (auth()->user()->hasRole('sisicha')) {
            return redirect()->route('adminRadio');
        }

        if (auth()->user()->hasRole('biblioteca')) {
            return redirect()->route('bibliotecario');
        }

        if (auth()->user()->hasRole('videos')) {
            return redirect()->route('adminVideos');
        }

        if (auth()->user()->hasRole('audios')) {
            return redirect()->route('adminCanciones');
        }

        if (auth()->user()->hasRole('kawsay')) {
            return redirect()->route('adminKawsay');
        }

        // Sin panel propio (ej. alumno): pantalla de bienvenida genérica.
        return view('home');
    }
}
