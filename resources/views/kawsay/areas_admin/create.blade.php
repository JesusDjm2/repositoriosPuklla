@extends('layouts.app')
@section('titulo', '- Nueva área Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Nueva área" subtitulo="El punto de partida de la enciclopedia: todo lo demás (temas, archivos) cuelga de acá"
        icono="fa-th-large" color="kawsay" />

    <div class="bv-form-card">
        <form action="{{ route('kawsayareas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="col-md-9 form-group">
                    <label class="bv-form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required maxlength="255" value="{{ old('nombre') }}">
                    @error('nombre')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 form-group">
                    <label class="bv-form-label">Color del borde</label>
                    <input type="color" name="color" class="form-control form-control-sm" style="height:calc(1.5em + .5rem + 2px);" value="{{ old('color', '#6e7d1e') }}">
                    @error('color')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="bv-form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="3"
                    placeholder="Frase corta que resume el área, se muestra debajo del círculo en la portada.">{{ old('descripcion') }}</textarea>
                @error('descripcion')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="bv-form-label">Imagen del círculo</label>
                <input type="file" name="imagen" class="form-control-file" accept="image/*">
                <small class="text-muted">Se muestra dentro del círculo de la portada. Si no subes una, se muestra el ícono.</small>
                @error('imagen')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="bv-form-actions">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> Guardar área
                </button>
                <a href="{{ route('kawsayareas.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
