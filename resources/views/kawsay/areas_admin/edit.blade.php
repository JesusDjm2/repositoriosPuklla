@extends('layouts.app')
@section('titulo', '- Editar área Kawsay')


@section('contenido')

    <x-hero-banner :titulo="$area->nombre" subtitulo="Editar área" icono="fa-th-large" color="kawsay">
        <x-slot:actions>
            <a href="{{ route('kawsay.area', $area->slug) }}" target="_blank" class="btn btn-sm btn-light">
                <i class="fa fa-eye"></i> Ver en el sitio
            </a>
        </x-slot:actions>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="bv-form-card">
        <form action="{{ route('kawsayareas.update', $area) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="col-md-9 form-group">
                    <label class="bv-form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required maxlength="255" value="{{ old('nombre', $area->nombre) }}">
                    @error('nombre')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 form-group">
                    <label class="bv-form-label">Color del borde</label>
                    <input type="color" name="color" class="form-control form-control-sm" style="height:calc(1.5em + .5rem + 2px);" value="{{ old('color', $area->color ?: '#6e7d1e') }}">
                    @error('color')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="bv-form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="3"
                    placeholder="Frase corta que resume el área, se muestra debajo del círculo en la portada.">{{ old('descripcion', $area->descripcion) }}</textarea>
                @error('descripcion')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="bv-form-label">Imagen del círculo</label>
                @if ($area->imagen)
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <img src="{{ asset($area->imagen) }}" alt="{{ $area->nombre }}" class="adm-avatar-ring" style="--ring-color: {{ $area->color ?: '#6e7d1e' }}">
                        <label class="mb-0 small">
                            <input type="checkbox" name="remove_imagen" value="1"> Quitar imagen actual
                        </label>
                    </div>
                @endif
                <input type="file" name="imagen" class="form-control-file" accept="image/*">
                <small class="text-muted">Se muestra dentro del círculo de la portada. Si no subes una, se muestra el ícono.</small>
                @error('imagen')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="bv-form-actions">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> Guardar cambios
                </button>
                <a href="{{ route('kawsayareas.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
            </div>
        </form>
    </div>

    <div class="bv-form-card">
        <div class="bv-form-section-title">Temas de esta área ({{ $area->temas()->count() }})</div>
        @if ($area->temas()->count() === 0)
            <p class="text-muted mb-0">Todavía no hay temas en esta área.</p>
        @else
            <div class="d-flex flex-wrap gap-2">
                @foreach ($area->temas as $tema)
                    <a href="{{ route('kawsaytemas.edit', $tema) }}" class="btn btn-sm btn-outline-secondary">
                        {{ $tema->titulo }}
                    </a>
                @endforeach
            </div>
        @endif
        <a href="{{ route('kawsaytemas.create') }}" class="btn btn-sm btn-link mt-2 px-0">
            <i class="fa fa-plus"></i> Agregar un tema a esta área
        </a>
    </div>

@endsection
