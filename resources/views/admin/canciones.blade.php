@extends('layouts.app')
@section('titulo', '- Panel Canciones')
@section('contenido')

    <x-hero-banner titulo="Panel de Canciones & Audios" subtitulo="Gestión del repositorio musical, grabaciones y equipo"
        icono="fa-music" color="radio">
        <x-slot:stats>
            @php
                $iconosStats = [
                    'Canciones' => 'fa-music',
                    'Categorías de canciones' => 'fa-tags',
                ];
            @endphp
            @foreach ($conteos as $etiqueta => $valor)
                <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-music'" :valor="$valor" :etiqueta="$etiqueta" />
            @endforeach
        </x-slot:stats>
    </x-hero-banner>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <x-dash-card
                href="{{ route('canciones.index') }}"
                icono="fa-music"
                variante="canciones"
                titulo="Canciones"
                descripcion="Catálogo musical, audios educativos, temas tradicionales y grabaciones.">
                <x-slot:badges>
                    <span class="stat-badge badge-registros">
                        <i class="fa fa-music"></i> {{ $conteos['Canciones'] ?? 0 }} temas
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
                descripcion="Clasificación por géneros musicales y proyectos pedagógicos.">
                <x-slot:badges>
                    <span class="stat-badge badge-categorias">
                        <i class="fa fa-tags"></i> {{ $conteos['Categorías de canciones'] ?? 0 }} categorías
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
                    descripcion="Usuarios con permisos para administrar audios y canciones.">
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
