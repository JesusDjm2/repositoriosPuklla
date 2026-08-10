@extends('layouts.app')
@section('titulo', '- Panel Kawsay')
@section('contenido')

    <x-hero-banner titulo="Panel Kawsay" subtitulo="Gestión del catálogo y del equipo Kawsay" icono="fa-leaf" />

    <div class="modules-grid" data-animate-stagger>
        <x-module-card href="{{ route('kawsaylibros.index') }}" icono="fa-leaf"
            titulo="Libros Kawsay" descripcion="Catálogo de libros del proyecto Kawsay" />
        <x-module-card href="{{ route('admin.index') }}" icono="fa-users"
            titulo="Administradores" descripcion="Gestiona los usuarios con acceso a este panel"
            :badge="$admins->count()" />
    </div>

@endsection
