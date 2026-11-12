@extends('layouts.app')
@section('titulo', '- Panel Radio Pukllasunchis')

@section('contenido')

    @include('sisichakuna.partials.sisipedia-hero', [
        'conteosPanel' => $conteos
    ])

    @include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'panel'])

    <nav class="bv-crud-nav-grupo nav mb-3" id="radioTabs" role="tablist">
        <a class="bv-crud-tab-grupo active" id="tab-sisichakunay" data-toggle="tab" href="#panel-sisichakunay"
            role="tab" aria-controls="panel-sisichakunay" aria-selected="true">
            <i class="fa fa-th-list"></i> Sisichakunay (Sisipedia)
        </a>
        <a class="bv-crud-tab-grupo" id="tab-canciones" data-toggle="tab" href="#panel-canciones" role="tab"
            aria-controls="panel-canciones" aria-selected="false">
            <i class="fa fa-music"></i> Canciones
        </a>
        <a class="bv-crud-tab-grupo" id="tab-videos" data-toggle="tab" href="#panel-videos" role="tab"
            aria-controls="panel-videos" aria-selected="false">
            <i class="fa fa-video-camera"></i> Videos
        </a>
    </nav>

    <div class="tab-content">
        {{-- PESTAÑA SISICHAKUNAY / SISIPEDIA --}}
        <div class="tab-pane fade show active" id="panel-sisichakunay" role="tabpanel" aria-labelledby="tab-sisichakunay">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('sisipedia.categories.index') }}"
                        icono="fa-sitemap"
                        variante="sisicha"
                        titulo="Registros de Sisipedia"
                        descripcion="Árbol de saberes, categorías, relatos, fichas pedagógicas y contenidos de Sisipedia.">
                        <x-slot:badges>
                            <span class="stat-badge badge-radio">
                                <i class="fa fa-sitemap"></i> {{ number_format($conteos['Registros Sisipedia'] ?? 0) }} registros
                            </span>
                            <span class="stat-badge badge-pdf">
                                <i class="fa fa-file-pdf-o"></i> {{ number_format($conteos['Documentos PDF'] ?? 0) }} PDFs
                            </span>
                            <span class="stat-badge badge-audio">
                                <i class="fa fa-headphones"></i> {{ number_format($conteos['Audios Sisipedia'] ?? 0) }} audios
                            </span>
                            <span class="stat-badge badge-imagen">
                                <i class="fa fa-picture-o"></i> {{ number_format($conteos['Imágenes'] ?? 0) }} imágenes
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('sisipedia.aportaciones.index') }}"
                        icono="fa-inbox"
                        variante="aportaciones"
                        titulo="Aportaciones de la Comunidad"
                        descripcion="Contribuciones y testimonios pedagógicos enviados por docentes rurales y colaboradores.">
                        <x-slot:badges>
                            <span class="stat-badge {{ ($conteos['Aportaciones pendientes'] ?? 0) > 0 ? 'badge-atrasados' : 'badge-registros' }}">
                                <i class="fa fa-comments"></i> {{ $conteos['Aportaciones pendientes'] ?? 0 }} pendientes
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('sisipedia.busquedas.sin-resultados') }}"
                        icono="fa-search"
                        variante="busquedas"
                        titulo="Búsquedas sin Resultados"
                        descripcion="Monitoreo de términos y saberes consultados por usuarios que aún no tienen contenido registrado.">
                        <x-slot:badges>
                            <span class="stat-badge badge-info">
                                <i class="fa fa-eye"></i> Auditoría de demanda
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
            </div>
        </div>

        {{-- PESTAÑA CANCIONES --}}
        <div class="tab-pane fade" id="panel-canciones" role="tabpanel" aria-labelledby="tab-canciones">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('canciones.index') }}"
                        icono="fa-music"
                        variante="canciones"
                        titulo="Catálogo de Canciones"
                        descripcion="Producción musical, temas tradicionales andinos, audios pedagógicos y relatos grabados.">
                        <x-slot:badges>
                            <span class="stat-badge badge-registros">
                                <i class="fa fa-music"></i> {{ $conteos['Canciones'] ?? 0 }} canciones
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('cancionescat.index') }}"
                        icono="fa-tags"
                        variante="canciones"
                        titulo="Categorías de Canciones"
                        descripcion="Clasificación por géneros musicales, edades, temáticas y proyectos de audio.">
                        <x-slot:badges>
                            <span class="stat-badge badge-categorias">
                                <i class="fa fa-tags"></i> {{ $conteos['Categorías de canciones'] ?? 0 }} categorías
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>
            </div>
        </div>

        {{-- PESTAÑA VIDEOS --}}
        <div class="tab-pane fade" id="panel-videos" role="tabpanel" aria-labelledby="tab-videos">
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('videos.index') }}"
                        icono="fa-video-camera"
                        variante="videos"
                        titulo="Videos y Audiovisuales"
                        descripcion="Cápsulas pedagógicas, documentales de radio, registros visuales de comunidades y streaming.">
                        <x-slot:badges>
                            <span class="stat-badge badge-registros">
                                <i class="fa fa-play"></i> {{ $conteos['Videos'] ?? 0 }} videos
                            </span>
                        </x-slot:badges>
                    </x-dash-card>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <x-dash-card
                        href="{{ route('videoscat.index') }}"
                        icono="fa-tags"
                        variante="videos"
                        titulo="Categorías de Videos"
                        descripcion="Organización por series, programas radiales filmados y contenidos educativos.">
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

    {{-- EQUIPO --}}
    @if (!empty($admins) && $admins->isNotEmpty())
        <h2 class="h5 font-weight-bold mb-3 mt-2">Equipo de Radio & Sisichakunay</h2>
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('admin.index') }}"
                    icono="fa-users"
                    variante="usuarios"
                    titulo="Administradores de Radio"
                    descripcion="Usuarios con permisos sobre Sisipedia, grabaciones radiales y material audiovisual.">
                    <x-slot:badges>
                        <span class="stat-badge badge-secciones">
                            <i class="fa fa-user-circle"></i> {{ $admins->count() }} miembros
                        </span>
                    </x-slot:badges>
                </x-dash-card>
            </div>
        </div>
    @endif

@endsection
