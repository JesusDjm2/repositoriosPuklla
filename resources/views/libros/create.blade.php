@extends('layouts.app')
@section('titulo', '- Nuevo libro')
@section('contenido')
    <div data-animate-stagger>
        <x-page-header titulo="Nuevo libro" subtitulo="Regístralo y marca sus categorías">
            <x-slot:actions>
                <a href="{{ route('libros.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        <div class="admin-table-card p-4">
            <form action="{{ route('libros.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="col-md-12">
                        <x-form-field name="nombre" label="Nombre" :value="old('nombre')" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-field name="autor" label="Autor" :value="old('autor')" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-field name="identificador" label="Identificador (Google Drive)"
                            :value="old('identificador')" required />
                    </div>
                </div>

                <h2 class="h6 font-weight-bold mt-2 mb-3">Secciones y categorías</h2>
                <div class="row">
                    @foreach ($secciones as $seccion)
                        <div class="col-md-4 mb-3">
                            <div class="font-weight-bold mb-1">{{ $seccion->nombre }}</div>
                            @foreach ($seccion->categorias as $categoria)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categorias[]"
                                        id="categoria_{{ $categoria->id }}" value="{{ $categoria->id }}"
                                        {{ (is_array(old('categorias')) && in_array($categoria->id, old('categorias'))) || (isset($categoriaSeleccionada) && in_array($categoria->id, $categoriaSeleccionada)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="categoria_{{ $categoria->id }}">
                                        {{ $categoria->nombre }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @error('categorias')
                    <div class="text-danger small mb-3">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="fa fa-check mr-1"></i> Guardar
                </button>
            </form>
        </div>
    </div>
@endsection
