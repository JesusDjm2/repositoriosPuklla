@extends('layouts.app')
@section('titulo', 'Crear nuevo Libro Kawsay')
@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-info">Crear Nuevo Libro Kawsay
                <a href="{{ route('kawsaylibros.index') }}" class="btn btn-danger btn-sm float-right">Volver</a>
            </h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div style="border-bottom: 1px dashed rgb(74, 113, 138); width: 100%;" class="mt-2"></div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('kawsaylibros.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-12 mt-3">
                        <label for="nombre">Nombre:</label>
                        <input type="text" name="nombre" id="nombre"
                            class="form-control form-control-sm rounded-pill" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-lg-6 mt-3">
                        <label for="autor">Autor:</label>
                        <input type="text" name="autor" id="autor"
                            class="form-control form-control-sm rounded-pill" value="{{ old('autor') }}" required>
                    </div>
                    <div class="col-lg-6 mt-3 mb-2">
                        <label for="identificador">Identificador:</label>
                        <input type="text" name="identificador" id="identificador"
                            class="form-control form-control-sm rounded-pill" value="{{ old('identificador') }}" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h5>Categorías:</h5>
                    </div>
                    @foreach ($categorias as $categoria)
                        <div class="col-lg-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categorias[]"
                                    id="categoria_{{ $categoria->id }}" value="{{ $categoria->id }}"
                                    {{ (is_array(old('categorias')) && in_array($categoria->id, old('categorias'))) || (isset($categoriaSeleccionada) && in_array($categoria->id, $categoriaSeleccionada)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="categoria_{{ $categoria->id }}">
                                    {{ $categoria->nombre }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
            </form>
        </div>
    </div>
@endsection
