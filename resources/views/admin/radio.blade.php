@extends('layouts.app')
@section('titulo', '- Panel Radio Pukllasunchis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Panel Radio Pukllasunchis" subtitulo="Gestión de Sisichakunay, Canciones y Videos"
        icono="fa-broadcast-tower" color="radio">
        <x-slot:stats>
            @php
                $iconosStats = [
                    'Videos' => 'fa-video-camera',
                    'Categorías de video' => 'fa-tags',
                    'Canciones' => 'fa-music',
                    'Categorías de canciones' => 'fa-tags',
                    'Registros Sisipedia' => 'fa-th-list',
                    'Aportaciones pendientes' => 'fa-inbox',
                ];
            @endphp
            @foreach ($conteos as $etiqueta => $valor)
                <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-broadcast-tower'" :valor="$valor" :etiqueta="$etiqueta" />
            @endforeach
        </x-slot:stats>
    </x-hero-banner>

    <nav class="bv-crud-nav-grupo nav mb-2" id="radioTabs" role="tablist">
        <a class="bv-crud-tab-grupo active" id="tab-sisichakunay" data-toggle="tab" href="#panel-sisichakunay"
            role="tab" aria-controls="panel-sisichakunay" aria-selected="true">
            <i class="fa fa-th-list"></i> Sisichakunay
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
        <div class="tab-pane fade show active" id="panel-sisichakunay" role="tabpanel" aria-labelledby="tab-sisichakunay">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('sisipedia.categories.index') }}" icono="fa-th-list" color="radio"
                    titulo="Registros" descripcion="Categorías y contenidos de Sisipedia" />
                <x-module-card href="{{ route('sisipedia.aportaciones.index') }}" icono="fa-inbox" color="radio"
                    titulo="Aportaciones" descripcion="Contribuciones enviadas por la comunidad" />
            </div>
        </div>
        <div class="tab-pane fade" id="panel-canciones" role="tabpanel" aria-labelledby="tab-canciones">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('canciones.index') }}" icono="fa-music" color="radio"
                    titulo="Canciones" descripcion="Catálogo de canciones" />
                <x-module-card href="{{ route('cancionescat.index') }}" icono="fa-tags" color="radio"
                    titulo="Categorías de canciones" descripcion="Categorías del catálogo de canciones" />
            </div>
        </div>
        <div class="tab-pane fade" id="panel-videos" role="tabpanel" aria-labelledby="tab-videos">
            <div class="modules-grid" data-animate-stagger>
                <x-module-card href="{{ route('videos.index') }}" icono="fa-video-camera" color="radio"
                    titulo="Videos" descripcion="Catálogo de videos" />
                <x-module-card href="{{ route('videoscat.index') }}" icono="fa-tags" color="radio"
                    titulo="Categorías de video" descripcion="Categorías del catálogo de videos" />
            </div>
        </div>
    </div>

    <h2 class="h5 font-weight-bold mb-3 mt-3">Equipo</h2>
    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users" color="radio"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
