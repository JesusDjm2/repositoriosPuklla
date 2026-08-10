@extends('layouts.app')
@section('titulo', '- Editar categoría')
@section('contenido')
    <div data-animate-stagger>
        <x-page-header titulo="Editar categoría" :subtitulo="$categoria->nombre">
            <x-slot:actions>
                <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        <div class="admin-table-card p-4">
            <form action="{{ route('categorias.update', $categoria->id) }}" method="POST">
                @csrf
                @method('PUT')
                <x-form-field name="nombre" label="Nombre" :value="old('nombre', $categoria->nombre)" required
                    data-slug-source />
                <x-form-field name="url" label="URL" :value="old('url', $categoria->url)" required data-slug-target
                    readonly />
                <x-form-field type="select" name="seccion_id" label="Sección"
                    :options="$secciones->pluck('nombre', 'id')" :selected="old('seccion_id', $categoria->seccion_id)"
                    required />

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="fa fa-check mr-1"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
@endsection
