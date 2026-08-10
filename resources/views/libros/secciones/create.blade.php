@extends('layouts.app')
@section('titulo', '- Nueva sección')
@section('contenido')
    <div data-animate-stagger>
        <x-page-header titulo="Nueva sección" subtitulo="Agrupa un conjunto de categorías">
            <x-slot:actions>
                <a href="{{ route('secciones.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        <div class="admin-table-card p-4">
            <form action="{{ route('secciones.store') }}" method="POST">
                @csrf
                <x-form-field name="nombre" label="Nombre" :value="old('nombre')" required data-slug-source />
                <x-form-field name="url" label="URL" :value="old('url')" required data-slug-target readonly />

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="fa fa-check mr-1"></i> Guardar
                </button>
            </form>
        </div>
    </div>
@endsection
