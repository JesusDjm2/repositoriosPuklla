@extends('layouts.app')
@section('titulo', '- Panel Biblioteca')

@push('styles')
    {{-- Mismo lenguaje visual de pestañas que las páginas de listado de
         Biblioteca (ver partials/biblioteca-*-crud-header): el switch
         Biblioteca digital/física de abajo reusa esas clases .bv-crud-tab-*
         en vez de un estilo propio, para que no cambie de aspecto al
         entrar a una sección concreta. --}}
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Panel de Biblioteca" subtitulo="Gestión del catálogo y del equipo bibliotecario"
        icono="fa-book" color="gold">
        <x-slot:stats>
            @php
                $iconosStats = [
                    'Libros digitales' => 'fa-book',
                    'Libros físicos' => 'fa-archive',
                    'Categorías' => 'fa-tags',
                    'Préstamos activos' => 'fa-hand-holding',
                    'Préstamos atrasados' => 'fa-exclamation-triangle',
                ];
            @endphp
            @foreach ($conteos as $etiqueta => $valor)
                <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-book'" :valor="$valor" :etiqueta="$etiqueta" />
            @endforeach
        </x-slot:stats>
    </x-hero-banner>

    <nav class="bv-crud-nav-grupo nav mb-2" id="bibliotecaTabs" role="tablist">
        <a class="bv-crud-tab-grupo active" id="tab-biblioteca-digital" data-toggle="tab" href="#panel-biblioteca-digital"
            role="tab" aria-controls="panel-biblioteca-digital" aria-selected="true">
            <i class="fa fa-laptop"></i> Biblioteca digital
        </a>
        <a class="bv-crud-tab-grupo" id="tab-biblioteca-fisica" data-toggle="tab" href="#panel-biblioteca-fisica"
            role="tab" aria-controls="panel-biblioteca-fisica" aria-selected="false">
            <i class="fa fa-archive"></i> Biblioteca física
        </a>
    </nav>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="panel-biblioteca-digital" role="tabpanel"
            aria-labelledby="tab-biblioteca-digital">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('secciones.index') }}" icono="fa-list" color="gold"
                    titulo="Secciones" descripcion="Grandes bloques temáticos del catálogo digital" />
                <x-module-card href="{{ route('categorias.index') }}" icono="fa-tags" color="gold"
                    titulo="Categorías" descripcion="Categorías dentro de cada sección" />
                <x-module-card href="{{ route('libros.index') }}" icono="fa-book" color="gold"
                    titulo="Libros" descripcion="Catálogo de libros y publicaciones digitales" />
            </div>
        </div>
        <div class="tab-pane fade" id="panel-biblioteca-fisica" role="tabpanel"
            aria-labelledby="tab-biblioteca-fisica">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('biblioteca-fisica.categorias.index') }}" icono="fa-archive" color="gold"
                    titulo="Categorías físicas" descripcion="Clasificación Dewey de los libros físicos" />
                <x-module-card href="{{ route('biblioteca-fisica.libros.index') }}" icono="fa-book" color="gold"
                    titulo="Libros físicos" descripcion="Catálogo de ejemplares en estantería" />
                <x-module-card href="{{ route('biblioteca-fisica.prestamos.index') }}" icono="fa-hand-holding" color="gold"
                    titulo="Préstamos" descripcion="Registro de préstamos y devoluciones" />
            </div>
        </div>
    </div>

    <h2 class="h5 font-weight-bold mb-3">Equipo</h2>
    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users" color="gold"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
