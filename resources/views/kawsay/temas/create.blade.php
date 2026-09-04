@extends('layouts.app')
@section('titulo', '- Nuevo tema Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Nuevo tema" subtitulo="Se mostrará dentro del área que elijas" icono="fa-map-signs" color="kawsay" />

    <div class="bv-form-card">
        <form action="{{ route('kawsaytemas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label class="bv-form-label">Área</label>
                    <select name="kawsay_area_id" class="form-control form-control-sm" required>
                        <option value="">Seleccionar área</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" {{ (int) old('kawsay_area_id', request('area')) === $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                    @error('area')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8 form-group">
                    <label class="bv-form-label">Título</label>
                    <input type="text" name="titulo" class="form-control form-control-sm" required maxlength="255" value="{{ old('titulo') }}">
                    @error('titulo')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="bv-form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="4"
                    placeholder="Cuéntale al visitante qué recursos va a encontrar para aprender, aplicar y compartir sobre este tema.">{{ old('descripcion') }}</textarea>
                @error('descripcion')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label class="bv-form-label">Imagen de portada</label>
                    <input type="file" name="imagen" class="form-control-file" accept="image/*">
                    <small class="text-muted">Se usa en la tarjeta del tema. Si no subes una, se muestra el ícono.</small>
                    @error('imagen')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label class="bv-form-label">Galería de imágenes</label>
                    <input type="file" name="galeria[]" class="form-control-file" accept="image/*" multiple>
                    <small class="text-muted">Se muestran en el hero de la vista del tema, como carrusel.</small>
                    @error('galeria.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="bv-form-actions">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> Guardar tema
                </button>
                <a href="{{ route('kawsaytemas.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
