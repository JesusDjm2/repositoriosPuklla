@extends('layouts.app')
@section('titulo', '- Panel Sisichakunay')
@section('contenido')

    <x-hero-banner titulo="Panel Sisichakunay" subtitulo="Gestión de Sisipedia" icono="fa-th-list" />

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('sisipedia.categories.index') }}" icono="fa-th-list"
            titulo="Registros" descripcion="Categorías y contenidos de Sisipedia" />
    </div>

@endsection
