@extends('layouts.app')
@section('titulo', '- Panel Videos')
@section('contenido')

    <x-hero-banner titulo="Panel de Videos" subtitulo="Gestión del catálogo y del equipo de Videos"
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

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('videos.index') }}" icono="fa-video-camera" color="radio"
            titulo="Videos" descripcion="Catálogo de videos" />
        <x-module-card href="{{ route('videoscat.index') }}" icono="fa-tags" color="radio"
            titulo="Categorías de video" descripcion="Categorías del catálogo de videos" />
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users" color="radio"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
