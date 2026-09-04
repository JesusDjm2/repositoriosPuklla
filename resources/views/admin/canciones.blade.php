@extends('layouts.app')
@section('titulo', '- Panel Canciones')
@section('contenido')

    <x-hero-banner titulo="Panel de Canciones" subtitulo="Gestión del catálogo y del equipo de Canciones"
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

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('canciones.index') }}" icono="fa-music" color="radio"
            titulo="Canciones" descripcion="Catálogo de canciones" />
        <x-module-card href="{{ route('cancionescat.index') }}" icono="fa-tags" color="radio"
            titulo="Categorías de canciones" descripcion="Categorías del catálogo de canciones" />
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users" color="radio"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
