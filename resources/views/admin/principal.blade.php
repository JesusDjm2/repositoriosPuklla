@extends('layouts.app')
@section('titulo', '- Panel Principal')
@section('contenido')

    <x-page-header titulo="Panel de administración" subtitulo="Asociación Pukllasunchis">
        <x-slot:actions>
            <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus mr-1"></i> Nuevo proyecto
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="stats-grid" data-animate-stagger>
        <x-stat-card icono="fa-sitemap" :valor="$totalProyectos" etiqueta="Proyectos activos" />
        <x-stat-card icono="fa-users" :valor="$totalUsuarios" etiqueta="Usuarios registrados" />
        <x-stat-card icono="fa-database"
            :valor="collect($conteos)->flatMap(fn ($c) => $c->values())->sum()"
            etiqueta="Registros totales" />
    </div>

    <h2 class="h5 font-weight-bold mb-3">Proyectos de la Asociación</h2>
    <div class="proyectos-grid" data-animate-stagger>
        @forelse ($proyectos as $proyecto)
            <x-project-card :proyecto="$proyecto" :conteos="$conteos[$proyecto->slug] ?? collect()" />
        @empty
            <p class="text-muted">Aún no hay proyectos registrados. Crea el primero con "Nuevo proyecto".</p>
        @endforelse
    </div>

@endsection
