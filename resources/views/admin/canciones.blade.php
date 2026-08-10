@extends('layouts.app')
@section('titulo', '- Panel Canciones')
@section('contenido')

    <x-hero-banner titulo="Panel de Canciones" subtitulo="Gestión del catálogo y del equipo de Canciones"
        icono="fa-music" />

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('canciones.index') }}" icono="fa-music"
            titulo="Canciones" descripcion="Catálogo de canciones" />
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
