@extends('layouts.app')
@section('titulo', '- Panel Videos')
@section('contenido')

    <x-hero-banner titulo="Panel de Videos" subtitulo="Gestión del catálogo audiovisual y del equipo de Videos"
        icono="fa-video-camera" color="radio">
        <x-slot:stats>
            @php
                $iconosStats = [
                    'Videos' => 'fa-video-camera',
                    'Categorías de video' => 'fa-tags',
                ];
            @endphp
            @foreach ($conteos as $etiqueta => $valor)
                <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-video-camera'" :valor="$valor" :etiqueta="$etiqueta" />
            @endforeach
        </x-slot:stats>
    </x-hero-banner>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <x-dash-card
                href="{{ route('videos.index') }}"
                icono="fa-video-camera"
                variante="videos"
                titulo="Videos"
                descripcion="Catálogo completo de materiales audiovisuales, conferencias y cápsulas.">
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
                titulo="Categorías de Video"
                descripcion="Clasificación temática de las producciones en video.">
                <x-slot:badges>
                    <span class="stat-badge badge-categorias">
                        <i class="fa fa-tags"></i> {{ $conteos['Categorías de video'] ?? 0 }} categorías
                    </span>
                </x-slot:badges>
            </x-dash-card>
        </div>

        @if (!empty($admins) && $admins->isNotEmpty())
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('admin.index') }}"
                    icono="fa-users"
                    variante="usuarios"
                    titulo="Administradores"
                    descripcion="Usuarios con permisos para administrar videos.">
                    <x-slot:badges>
                        <span class="stat-badge badge-secciones">
                            <i class="fa fa-user-circle"></i> {{ $admins->count() }} miembros
                        </span>
                    </x-slot:badges>
                </x-dash-card>
            </div>
        @endif
    </div>

@endsection
