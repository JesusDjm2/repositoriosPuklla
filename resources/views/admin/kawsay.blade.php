@extends('layouts.app')
@section('titulo', '- Panel Kawsay')

@section('contenido')

    <x-hero-banner titulo="Panel Kawsay" subtitulo="Gestión del repositorio educativo, catálogo y comunidad Kawsay"
        icono="fa-leaf" color="kawsay">
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
        <x-slot:actions>
            <a href="{{ route('kawsay') }}" target="_blank" rel="noopener" class="btn btn-light btn-sm">
                <i class="fa fa-external-link mr-1"></i> Portal público Kawsay
            </a>
        </x-slot:actions>
    </x-hero-banner>

    {{-- Repositorio educativo: Área → Tema → Archivo en flujo guiado --}}
    <h2 class="h5 font-weight-bold mb-1">Repositorio educativo</h2>
    <p class="text-muted mb-3" style="font-size:.9rem;">
        Flujo de creación pedagógica: cada nivel organiza y contiene al siguiente.
    </p>
    <div class="kawsay-flow" data-animate-stagger>
        <div class="kawsay-flow__step">
            <span class="kawsay-flow__num">1</span>
            <x-dash-card
                href="{{ route('kawsayareas.index') }}"
                icono="fa-th-large"
                variante="areas"
                titulo="Áreas"
                descripcion="Grandes ejes del repositorio: Territorio Vivo, Expresión Biocultural, etc.">
                <x-slot:badges>
                    <span class="stat-badge badge-kawsay">
                        <i class="fa fa-sitemap"></i> {{ $conteos['Áreas'] ?? 0 }} áreas
                    </span>
                </x-slot:badges>
            </x-dash-card>
        </div>
        <i class="fa fa-arrow-right kawsay-flow__arrow" aria-hidden="true"></i>
        <div class="kawsay-flow__step">
            <span class="kawsay-flow__num">2</span>
            <x-dash-card
                href="{{ route('kawsaytemas.index') }}"
                icono="fa-map-signs"
                variante="temas"
                titulo="Temas"
                descripcion="Módulos temáticos vinculados a cada área (ej. Biohuertos familiares).">
                <x-slot:badges>
                    <span class="stat-badge badge-taqe">
                        <i class="fa fa-book"></i> {{ $conteos['Temas'] ?? 0 }} temas
                    </span>
                </x-slot:badges>
            </x-dash-card>
        </div>
        <i class="fa fa-arrow-right kawsay-flow__arrow" aria-hidden="true"></i>
        <div class="kawsay-flow__step">
            <span class="kawsay-flow__num">3</span>
            <x-dash-card
                href="{{ route('kawsayarchivos.index') }}"
                icono="fa-folder-open"
                variante="archivos"
                titulo="Archivos"
                descripcion="Fichas didácticas, guías metodológicas, videos y presentaciones por tema.">
                <x-slot:badges>
                    <span class="stat-badge badge-registros">
                        <i class="fa fa-file-text"></i> {{ $conteos['Archivos'] ?? 0 }} recursos
                    </span>
                </x-slot:badges>
            </x-dash-card>
        </div>
    </div>

    {{-- Moderación de Comunidad --}}
    <h2 class="h5 font-weight-bold mb-1 mt-4">Comunidad y Participación</h2>
    <p class="text-muted mb-3" style="font-size:.9rem;">
        Comentarios y aportes reflexivos compartidos por docentes y estudiantes en los temas.
    </p>
    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <x-dash-card
                href="{{ route('kawsaycomentarios.index') }}"
                icono="fa-comments"
                variante="comentarios"
                titulo="Comentarios y Aportes"
                descripcion="Bandeja de moderación de comentarios públicos registrados en las fichas.">
                <x-slot:badges>
                    <span class="stat-badge badge-categorias">
                        <i class="fa fa-commenting-o"></i> {{ $conteos['Comentarios'] ?? 0 }} comentarios
                    </span>
                </x-slot:badges>
            </x-dash-card>
        </div>
    </div>

    {{-- Catálogo de Libros y Videos Kawsay --}}
    <h2 class="h5 font-weight-bold mb-1 mt-3">Catálogo de Libros y Videos</h2>
    <p class="text-muted mb-3" style="font-size:.9rem;">
        Biblioteca digital y acervo audiovisual especializado de Kawsay.
    </p>
    <nav class="bv-crud-nav-grupo nav mb-3" id="catalogoTabs" role="tablist">
        <a class="bv-crud-tab-grupo active" id="tab-catalogo-libros" data-toggle="tab" href="#panel-catalogo-libros"
            role="tab" aria-controls="panel-catalogo-libros" aria-selected="true">
            <i class="fa fa-book"></i> Libros Kawsay
        </a>
        <a class="bv-crud-tab-grupo" id="tab-catalogo-videos" data-toggle="tab" href="#panel-catalogo-videos"
            role="tab" aria-controls="panel-catalogo-videos" aria-selected="false">
            <i class="fa fa-video-camera"></i> Videos Kawsay
        </a>
    </nav>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="panel-catalogo-libros" role="tabpanel" aria-labelledby="tab-catalogo-libros">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('kawsaylibros.index') }}"
                        icono="fa-book"
                        variante="virtual"
                        titulo="Libros Kawsay"
                        descripcion="Publicaciones digitales y literatura especializada del proyecto Kawsay.">
                        <x-slot:badges>
                            <span class="stat-badge badge-registros">
                                <i class="fa fa-file-text-o"></i> {{ $conteos['Libros'] ?? 0 }} libros
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('kawsaycategorias.index') }}"
                        icono="fa-tags"
                        variante="virtual"
                        titulo="Categorías de Libros"
                        descripcion="Líneas editoriales y clasificaciones temáticas de libros Kawsay.">
                        <x-slot:badges>
                            <span class="stat-badge badge-categorias">
                                <i class="fa fa-tag"></i> {{ $conteos['Categorías'] ?? 0 }} categorías
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="panel-catalogo-videos" role="tabpanel" aria-labelledby="tab-catalogo-videos">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('kawsayvideos.index') }}"
                        icono="fa-video-camera"
                        variante="videos"
                        titulo="Videos Kawsay"
                        descripcion="Documentales, saberes bioculturales en video y cápsulas educativas.">
                        <x-slot:badges>
                            <span class="stat-badge badge-registros">
                                <i class="fa fa-play"></i> {{ $conteos['Videos'] ?? 0 }} videos
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('kawsayvideoscat.index') }}"
                        icono="fa-tags"
                        variante="videos"
                        titulo="Categorías de Video"
                        descripcion="Agrupamiento temático de materiales audiovisuales de Kawsay.">
                        <x-slot:badges>
                            <span class="stat-badge badge-categorias">
                                <i class="fa fa-tags"></i> {{ $conteos['Categorías de video'] ?? 0 }} categorías
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
            </div>
        </div>
    </div>

@endsection
