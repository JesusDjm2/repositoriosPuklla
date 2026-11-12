@extends('layouts.app')
@section('titulo', '- Panel Biblioteca EESP')


@section('contenido')

    <x-hero-banner titulo="Panel de Biblioteca EESP"
        subtitulo="Gestión integral de acervo digital, libros físicos, material pedagógico, tesis Taq'e y préstamos"
        icono="fa-book" color="gold">
        <x-slot:stats>
            <x-hero-stat icono="fa-laptop" :valor="$statsData['libros']" etiqueta="Libros digitales" />
            <x-hero-stat icono="fa-archive" :valor="$statsData['libros_fisicos']" etiqueta="Libros físicos" />
            <x-hero-stat icono="fa-cubes" :valor="$statsData['materiales']" etiqueta="Material didáctico" />
            <x-hero-stat icono="fa-graduation-cap" :valor="$statsData['taqe_trabajos']" etiqueta="Tesis Taq'e" />
            <x-hero-stat icono="fa-handshake-o" :valor="$statsData['prestamos_activos']" etiqueta="Préstamos activos" />
            @if ($statsData['prestamos_atrasados'] > 0)
                <x-hero-stat icono="fa-exclamation-triangle" :valor="$statsData['prestamos_atrasados']" etiqueta="Préstamos atrasados" />
            @endif
        </x-slot:stats>
        <x-slot:actions>
            <a href="{{ route('catalogo-fisico') }}" target="_blank" rel="noopener" class="btn btn-light btn-sm">
                <i class="fa fa-external-link mr-1"></i> Catálogo público
            </a>
            <a href="{{ route('eesppukllasunchis') }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">
                <i class="fa fa-globe mr-1"></i> Portada EESP
            </a>
        </x-slot:actions>
    </x-hero-banner>

    {{-- Pestañas de Navegación del Panel --}}
    <nav class="bv-crud-nav-grupo nav mb-3" id="bibliotecaTabs" role="tablist">
        <a class="bv-crud-tab-grupo active" id="tab-resumen" data-toggle="tab" href="#panel-resumen"
            role="tab" aria-controls="panel-resumen" aria-selected="true">
            <i class="fa fa-th-large"></i> Todos los módulos
        </a>
        <a class="bv-crud-tab-grupo" id="tab-biblioteca-fisica" data-toggle="tab" href="#panel-biblioteca-fisica"
            role="tab" aria-controls="panel-biblioteca-fisica" aria-selected="false">
            <i class="fa fa-archive"></i> Biblioteca física & Material
        </a>
        <a class="bv-crud-tab-grupo" id="tab-biblioteca-digital" data-toggle="tab" href="#panel-biblioteca-digital"
            role="tab" aria-controls="panel-biblioteca-digital" aria-selected="false">
            <i class="fa fa-laptop"></i> Biblioteca digital
        </a>
        <a class="bv-crud-tab-grupo" id="tab-taqe" data-toggle="tab" href="#panel-taqe"
            role="tab" aria-controls="panel-taqe" aria-selected="false">
            <i class="fa fa-graduation-cap"></i> Taq'e (Investigaciones)
        </a>
        <a class="bv-crud-tab-grupo" id="tab-multimedia" data-toggle="tab" href="#panel-multimedia"
            role="tab" aria-controls="panel-multimedia" aria-selected="false">
            <i class="fa fa-play-circle"></i> Multimedia EESP
        </a>
    </nav>

    <div class="tab-content">
        {{-- PANEL 1: TODOS LOS MÓDULOS (DISEÑO Y ELEMENTOS UNIFICADOS DE BIBLIOTECA) --}}
        <div class="tab-pane fade show active" id="panel-resumen" role="tabpanel" aria-labelledby="tab-resumen">
            <div class="row">
                {{-- BIBLIOTECA FÍSICA --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('biblioteca-fisica.libros.index') }}"
                        icono="fa-archive"
                        variante="fisica"
                        titulo="Biblioteca Física"
                        descripcion="Catálogo en estantería, ejemplares con código de barras, autores y clasificación Dewey.">
                        <x-slot:badges>
                            <span class="stat-badge badge-categorias" title="Categorías físicas">
                                <i class="fa fa-folder"></i> {{ $statsData['categorias_fisicas'] }} categorías
                            </span>
                            <span class="stat-badge badge-registros" title="Ejemplares físicos">
                                <i class="fa fa-book"></i> {{ number_format($statsData['libros_fisicos']) }} libros
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                {{-- PRÉSTAMOS --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('biblioteca-fisica.prestamos.index') }}"
                        icono="fa-handshake-o"
                        variante="prestamos"
                        titulo="Préstamos & Devoluciones"
                        descripcion="Control de préstamos de libros físicos y material didáctico a docentes y estudiantes.">
                        <x-slot:badges>
                            <span class="stat-badge badge-registros" title="Total préstamos">
                                <i class="fa fa-list"></i> {{ $statsData['prestamos_total'] }} registros
                            </span>
                            <span class="stat-badge badge-secciones" title="Préstamos activos">
                                <i class="fa fa-clock-o"></i> {{ $statsData['prestamos_activos'] }} activos
                            </span>
                            @if ($statsData['prestamos_atrasados'] > 0)
                                <span class="stat-badge badge-atrasados" title="Préstamos atrasados">
                                    <i class="fa fa-exclamation-triangle"></i> {{ $statsData['prestamos_atrasados'] }} atrasados
                                </span>
                            @endif
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                {{-- MATERIAL DIDÁCTICO --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('biblioteca-fisica.materiales.index') }}"
                        icono="fa-cubes"
                        variante="material"
                        titulo="Material Didáctico"
                        descripcion="Inventario y control pedagógico de kits, juegos didácticos, láminas e instrumentos para aula.">
                        <x-slot:badges>
                            <span class="stat-badge badge-categorias" title="Categorías de material">
                                <i class="fa fa-tags"></i> {{ $statsData['material_categorias'] }} categorías
                            </span>
                            <span class="stat-badge badge-registros" title="Materiales registrados">
                                <i class="fa fa-cubes"></i> {{ $statsData['materiales'] }} materiales
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                {{-- BIBLIOTECA DIGITAL / VIRTUAL --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('libros.index') }}"
                        icono="fa-laptop"
                        variante="virtual"
                        titulo="Biblioteca Digital"
                        descripcion="Publicaciones digitales, enlaces a Google Drive, portadas, autores y secciones temáticas.">
                        <x-slot:badges>
                            <span class="stat-badge badge-secciones" title="Secciones temáticas">
                                <i class="fa fa-layer-group"></i> {{ $statsData['secciones'] }} secciones
                            </span>
                            <span class="stat-badge badge-categorias" title="Categorías">
                                <i class="fa fa-folder"></i> {{ $statsData['categorias'] }} cat.
                            </span>
                            <span class="stat-badge badge-registros" title="Libros digitales">
                                <i class="fa fa-file-text-o"></i> {{ $statsData['libros'] }} libros
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                {{-- TAQ'E: INVESTIGACIONES Y TESIS --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('admin.taqe.trabajos.index') }}"
                        icono="fa-graduation-cap"
                        variante="taqe"
                        titulo="Taq'e — Tesis e Investigaciones"
                        descripcion="Repositorio institucional de trabajos de estudiantes y egresados EIB (Inicial, Primaria, Quechua).">
                        <x-slot:badges>
                            <span class="stat-badge badge-taqe" title="Líneas y categorías">
                                <i class="fa fa-folder-open"></i> {{ $statsData['taqe_categorias'] }} categorías
                            </span>
                            <span class="stat-badge badge-registros" title="Trabajos de tesis y proyectos">
                                <i class="fa fa-graduation-cap"></i> {{ $statsData['taqe_trabajos'] }} trabajos
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                {{-- VIDEOS EESP PUKLLASUNCHIS --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('videos.index') }}"
                        icono="fa-video-camera"
                        variante="videos"
                        titulo="Videos Institucionales EESP"
                        descripcion="Registro de videos formativos, conferencias pedagógicas, enlaces de YouTube y Drive.">
                        <x-slot:badges>
                            <span class="stat-badge badge-categorias" title="Categorías de video">
                                <i class="fa fa-tags"></i> {{ $statsData['video_categorias'] }} categorías
                            </span>
                            <span class="stat-badge badge-registros" title="Videos registrados">
                                <i class="fa fa-play"></i> {{ $statsData['videos'] }} videos
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
            </div>
        </div>
            </div>
        </div>

        {{-- PANEL 2: BIBLIOTECA FÍSICA & MATERIAL --}}
        <div class="tab-pane fade" id="panel-biblioteca-fisica" role="tabpanel" aria-labelledby="tab-biblioteca-fisica">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('biblioteca-fisica.libros.index') }}" icono="fa-book" color="gold"
                    titulo="Libros físicos" descripcion="Catálogo de ejemplares en estantería con código Dewey y código de barras"
                    :badge="number_format($statsData['libros_fisicos'])" />
                <x-module-card href="{{ route('biblioteca-fisica.categorias.index') }}" icono="fa-archive" color="gold"
                    titulo="Categorías físicas" descripcion="Clasificación Dewey (000 al 900) y colecciones especiales de la Escuela"
                    :badge="$statsData['categorias_fisicas']" />
                <x-module-card href="{{ route('biblioteca-fisica.prestamos.index') }}" icono="fa-handshake-o" color="gold"
                    titulo="Préstamos" descripcion="Registro, control de entregas, seguimiento de atrasos y devolución"
                    :badge="$statsData['prestamos_activos'] . ' activos'" />
                <x-module-card href="{{ route('biblioteca-fisica.materiales.index') }}" icono="fa-cubes" color="gold"
                    titulo="Material didáctico" descripcion="Kits pedagógicos, láminas y material didáctico por categoría"
                    :badge="$statsData['materiales']" />
            </div>
        </div>

        {{-- PANEL 3: BIBLIOTECA DIGITAL --}}
        <div class="tab-pane fade" id="panel-biblioteca-digital" role="tabpanel" aria-labelledby="tab-biblioteca-digital">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('libros.index') }}" icono="fa-book" color="gold"
                    titulo="Libros digitales" descripcion="Catálogo de publicaciones digitales y textos en PDF"
                    :badge="$statsData['libros']" />
                <x-module-card href="{{ route('categorias.index') }}" icono="fa-tags" color="gold"
                    titulo="Categorías virtuales" descripcion="Categorías dentro de cada sección temática digital"
                    :badge="$statsData['categorias']" />
                <x-module-card href="{{ route('secciones.index') }}" icono="fa-list" color="gold"
                    titulo="Secciones virtuales" descripcion="Grandes bloques temáticos organizadores de la biblioteca digital"
                    :badge="$statsData['secciones']" />
            </div>
        </div>

        {{-- PANEL 4: TAQ'E --}}
        <div class="tab-pane fade" id="panel-taqe" role="tabpanel" aria-labelledby="tab-taqe">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('admin.taqe.trabajos.index') }}" icono="fa-graduation-cap" color="gold"
                    titulo="Trabajos de estudiantes" descripcion="Tesis, monografías, relatos y proyectos pedagógicos bilingües"
                    :badge="$statsData['taqe_trabajos']" />
                <x-module-card href="{{ route('admin.taqe.categorias.index') }}" icono="fa-folder-open" color="gold"
                    titulo="Categorías de investigación" descripcion="Líneas temáticas: Saberes ancestrales, Estrategias pedagógicas, etc."
                    :badge="$statsData['taqe_categorias']" />
                <x-module-card href="{{ route('taqe.index') }}" icono="fa-external-link" color="gold"
                    titulo="Ver catálogo público Taq'e" descripcion="Vista pública para estudiantes, docentes e investigadores" />
            </div>
        </div>

        {{-- PANEL 5: MULTIMEDIA EESP --}}
        <div class="tab-pane fade" id="panel-multimedia" role="tabpanel" aria-labelledby="tab-multimedia">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('videos.index') }}" icono="fa-video-camera" color="gold"
                    titulo="Videos EESP" descripcion="Catálogo de conferencias, registros audiovisuales e institucionales"
                    :badge="$statsData['videos']" />
                <x-module-card href="{{ route('videoscat.index') }}" icono="fa-tags" color="gold"
                    titulo="Categorías de video" descripcion="Clasificación temática de los videos de la Escuela"
                    :badge="$statsData['video_categorias']" />
                <x-module-card href="{{ route('canciones.index') }}" icono="fa-music" color="gold"
                    titulo="Canciones & Audios" descripcion="Audios, temas musicales y repertorio institucional"
                    :badge="$statsData['canciones']" />
            </div>
        </div>
    </div>

    {{-- Equipo del proyecto --}}
    <h2 class="h5 font-weight-bold mb-3 mt-4">Equipo de Biblioteca</h2>
    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users" color="gold"
            titulo="Equipo de Administradores y Bibliotecarios" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count() . ' miembros'" />
    </div>

@endsection
