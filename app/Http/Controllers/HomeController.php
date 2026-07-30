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
        if (auth()->user()->hasRole('sisicha')) {
            return redirect()->route('sisipedia.categories.index');
        }

        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.principal');
        }

        return view('home');
    }
}
