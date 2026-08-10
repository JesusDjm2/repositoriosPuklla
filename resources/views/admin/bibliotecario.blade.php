@extends('layouts.app')
@section('titulo', '- Panel Biblioteca')
@section('contenido')

    <x-hero-banner titulo="Panel de Biblioteca" subtitulo="Gestión del catálogo y del equipo bibliotecario"
        icono="fa-book" />

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('libros.index') }}" icono="fa-book"
            titulo="Libros" descripcion="Catálogo de libros y publicaciones" />
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
