@extends('layouts.app')
@section('titulo', '- Panel Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Panel Kawsay" subtitulo="Gestión del catálogo y del equipo Kawsay" icono="fa-leaf"
        color="kawsay">
        <x-slot:stats>
            @php
                $iconosStats = [
                    'Áreas' => 'fa-th-large',
                    'Temas' => 'fa-map-signs',
                    'Archivos' => 'fa-folder-open',
                    'Comentarios' => 'fa-comments',
                    'Libros' => 'fa-book',
                    'Categorías' => 'fa-tags',
                    'Videos' => 'fa-video-camera',
                    'Categorías de video' => 'fa-tags',
                ];
            @endphp
            @foreach ($conteos as $etiqueta => $valor)
                <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-leaf'" :valor="$valor" :etiqueta="$etiqueta" />
            @endforeach
        </x-slot:stats>
    </x-hero-banner>

    {{-- ═══ Repositorio educativo: Área → Tema → Archivo. Se muestran como un
         flujo numerado (no una grilla suelta) porque cada nivel depende del
         anterior — no tiene sentido crear un Tema sin haber creado antes su
         Área, ni subir un Archivo sin haber creado antes su Tema. ═══ --}}
    <h2 class="h5 font-weight-bold mb-1">Repositorio educativo</h2>
    <p class="text-muted mb-3" style="font-size:.9rem;">
        Este es el orden en el que se crean los registros — cada paso pertenece al anterior.
    </p>
    <div class="kawsay-flow" data-animate-stagger>
        <div class="kawsay-flow__step">
            <span class="kawsay-flow__num">1</span>
            <x-module-card href="{{ route('kawsayareas.index') }}" icono="fa-th-large" color="kawsay"
                titulo="Áreas" descripcion="Las 4 grandes áreas del repositorio (Territorio Vivo, Expresión Biocultural...)" />
        </div>
        <i class="fa fa-arrow-right kawsay-flow__arrow"></i>
        <div class="kawsay-flow__step">
            <span class="kawsay-flow__num">2</span>
            <x-module-card href="{{ route('kawsaytemas.index') }}" icono="fa-map-signs" color="kawsay"
                titulo="Temas" descripcion="Cada tema pertenece a un área (ej. Biohuertos, dentro de Gestión Sostenible)" />
        </div>
        <i class="fa fa-arrow-right kawsay-flow__arrow"></i>
        <div class="kawsay-flow__step">
            <span class="kawsay-flow__num">3</span>
            <x-module-card href="{{ route('kawsayarchivos.index') }}" icono="fa-folder-open" color="kawsay"
                titulo="Archivos" descripcion="Fichas, teoría, videos y presentaciones — cada uno pertenece a un tema" />
        </div>
    </div>

    {{-- ═══ Moderación: no pertenece al flujo Área→Tema→Archivo (no crea nada
         nuevo, revisa lo que la gente ya comentó en los temas), por eso va
         aparte y no como un paso numerado más. ═══ --}}
    <h2 class="h5 font-weight-bold mb-1 mt-4">Comunidad</h2>
    <p class="text-muted mb-3" style="font-size:.9rem;">
        Comentarios que deja el público en los temas del repositorio educativo.
    </p>
    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('kawsaycomentarios.index') }}" icono="fa-comments" color="kawsay"
            titulo="Comentarios" descripcion="Todos los comentarios públicos de los temas, en un solo lugar" />
    </div>

    {{-- ═══ Catálogo de Libros y Videos: sistema aparte (no se relaciona con
         Área/Tema/Archivo de arriba), con su propio par Categoría → Contenido
         por cada tipo. Separado visualmente para no mezclar ambas
         jerarquías. ═══ --}}
    <h2 class="h5 font-weight-bold mb-1 mt-4">Catálogo de Libros y Videos</h2>
    <p class="text-muted mb-3" style="font-size:.9rem;">
        Sistema independiente del repositorio educativo de arriba, con sus propias categorías.
    </p>
    <nav class="bv-crud-nav-grupo nav mb-2" id="catalogoTabs" role="tablist">
        <a class="bv-crud-tab-grupo active" id="tab-catalogo-libros" data-toggle="tab" href="#panel-catalogo-libros"
            role="tab" aria-controls="panel-catalogo-libros" aria-selected="true">
            <i class="fa fa-book"></i> Libros
        </a>
        <a class="bv-crud-tab-grupo" id="tab-catalogo-videos" data-toggle="tab" href="#panel-catalogo-videos"
            role="tab" aria-controls="panel-catalogo-videos" aria-selected="false">
            <i class="fa fa-video-camera"></i> Videos
        </a>
    </nav>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="panel-catalogo-libros" role="tabpanel" aria-labelledby="tab-catalogo-libros">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('kawsaycategorias.index') }}" icono="fa-tags" color="kawsay"
                    titulo="Categorías" descripcion="Categorías del catálogo de libros" />
                <x-module-card href="{{ route('kawsaylibros.index') }}" icono="fa-book" color="kawsay"
                    titulo="Libros" descripcion="Catálogo de libros del proyecto Kawsay" />
            </div>
        </div>
        <div class="tab-pane fade" id="panel-catalogo-videos" role="tabpanel" aria-labelledby="tab-catalogo-videos">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('kawsayvideoscat.index') }}" icono="fa-tags" color="kawsay"
                    titulo="Categorías de video" descripcion="Categorías del catálogo de videos" />
                <x-module-card href="{{ route('kawsayvideos.index') }}" icono="fa-video-camera" color="kawsay"
                    titulo="Videos" descripcion="Catálogo de videos de Kawsay" />
            </div>
        </div>
    </div>

    <style>
        .kawsay-flow {
            display: flex;
            align-items: stretch;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .kawsay-flow__step {
            position: relative;
            flex: 1 1 0;
            min-width: 0;
        }
        .kawsay-flow__step .module-card {
            height: 100%;
        }
        .kawsay-flow__num {
            position: absolute;
            top: -10px;
            left: -10px;
            z-index: 1;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #6e7d1e;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
        }
        .kawsay-flow__arrow {
            align-self: center;
            color: #adb5bd;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        @media (max-width: 767.98px) {
            .kawsay-flow { flex-direction: column; }
            .kawsay-flow__arrow { transform: rotate(90deg); align-self: center; }
        }
    </style>

@endsection
