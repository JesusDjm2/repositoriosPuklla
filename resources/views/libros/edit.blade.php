@extends('layouts.app')
@section('titulo', '- Editar libro')
@section('contenido')
    <div data-animate-stagger>
        <x-page-header titulo="Editar libro" :subtitulo="$libro->nombre">
            <x-slot:actions>
                <a href="{{ route('libros.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        <div class="admin-table-card p-4">
            <form action="{{ route('libros.update', $libro->id) }}" method="POST">
                @csrf
                @method('PUT')
                <x-form-field name="nombre" label="Nombre" :value="old('nombre', $libro->nombre)" required />
                <x-form-field name="autor" label="Autor" :value="old('autor', $libro->autor)" required />
                <x-form-field name="identificador" label="Identificador (Google Drive)"
                    :value="old('identificador', $libro->identificador)" required />

                <div class="admin-form-field">
                    <label>Categorías</label>
                    <div class="d-flex flex-wrap" style="gap: .5rem 1.5rem;">
                        @foreach ($categorias as $categoria)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categorias[]"
                                    id="categoria_{{ $categoria->id }}" value="{{ $categoria->id }}"
                                    {{ (is_array(old('categorias')) && in_array($categoria->id, old('categorias'))) || (isset($libroCategorias) && in_array($categoria->id, $libroCategorias)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="categoria_{{ $categoria->id }}">
                                    {{ $categoria->nombre }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('categorias')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="fa fa-check mr-1"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
@endsection
