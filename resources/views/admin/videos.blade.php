@extends('layouts.app')
@section('titulo', '- Panel Videos')
@section('contenido')

    <x-hero-banner titulo="Panel de Videos" subtitulo="Gestión del catálogo y del equipo de Videos"
        icono="fa-video-camera" />

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('videos.index') }}" icono="fa-video-camera"
            titulo="Videos" descripcion="Catálogo de videos" />
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
