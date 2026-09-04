@extends('layouts.app')
@section('titulo', '- Panel Sisichakunay')
@section('contenido')

    <x-hero-banner titulo="Panel Sisichakunay" subtitulo="Gestión de Sisipedia" icono="fa-th-list" color="radio">
        <x-slot:stats>
            @php
                $iconosStats = [
                    'Registros Sisipedia' => 'fa-th-list',
                    'Aportaciones pendientes' => 'fa-inbox',
                ];
            @endphp
            @foreach ($conteos as $etiqueta => $valor)
                <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-th-list'" :valor="$valor" :etiqueta="$etiqueta" />
            @endforeach
        </x-slot:stats>
    </x-hero-banner>

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('sisipedia.categories.index') }}" icono="fa-th-list" color="radio"
            titulo="Registros" descripcion="Categorías y contenidos de Sisipedia" />
        <x-module-card href="{{ route('sisipedia.aportaciones.index') }}" icono="fa-inbox" color="radio"
            titulo="Aportaciones" descripcion="Contribuciones enviadas por la comunidad" />
    </div>

@endsection
