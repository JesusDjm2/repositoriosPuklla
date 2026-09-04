@extends('layouts.app')
@section('titulo', '- Editar tema Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner :titulo="$tema->titulo" subtitulo="Editar tema" icono="fa-map-signs" color="kawsay">
        <x-slot:actions>
            <a href="{{ route('kawsay.tema', [$tema->area->slug, $tema->slug]) }}" target="_blank" class="btn btn-sm btn-light">
                <i class="fa fa-eye"></i> Ver en el sitio
            </a>
        </x-slot:actions>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="bv-form-card">
        <form action="{{ route('kawsaytemas.update', $tema) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label class="bv-form-label">Área</label>
                    <select name="kawsay_area_id" class="form-control form-control-sm" required>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" {{ (int) old('kawsay_area_id', $tema->kawsay_area_id) === $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                    @error('kawsay_area_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8 form-group">
                    <label class="bv-form-label">Título</label>
                    <input type="text" name="titulo" class="form-control form-control-sm" required maxlength="255" value="{{ old('titulo', $tema->titulo) }}">
                    @error('titulo')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="bv-form-label">Descripción</label>
                <textarea name="descripcion" class="form-control form-control-sm" rows="4"
                    placeholder="Cuéntale al visitante qué recursos va a encontrar para aprender, aplicar y compartir sobre este tema.">{{ old('descripcion', $tema->descripcion) }}</textarea>
                @error('descripcion')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label class="bv-form-label">Imagen de portada</label>
                    @if ($tema->imagen)
                        <div class="mb-2 d-flex align-items-center gap-2">
                            <img src="{{ asset($tema->imagen) }}" alt="{{ $tema->titulo }}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;">
                            <label class="mb-0 small">
                                <input type="checkbox" name="remove_imagen" value="1"> Quitar imagen actual
                            </label>
                        </div>
                    @endif
                    <input type="file" name="imagen" class="form-control-file" accept="image/*">
                    @error('imagen')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label class="bv-form-label">Agregar imágenes a la galería</label>
                    <input type="file" name="galeria[]" class="form-control-file" accept="image/*" multiple>
                    <small class="text-muted">Se suman a las que ya tiene el tema (abajo).</small>
                    @error('galeria.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="bv-form-actions">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> Guardar cambios
                </button>
                <a href="{{ route('kawsaytemas.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
            </div>
        </form>
    </div>

    <div class="bv-form-card">
        <div class="bv-form-section-title">Galería actual ({{ $tema->imagenes->count() }})</div>
        @if ($tema->imagenes->isEmpty())
            <p class="text-muted mb-0">Todavía no hay imágenes en la galería.</p>
        @else
            <div class="d-flex flex-wrap gap-3">
                @foreach ($tema->imagenes as $img)
                    <div class="text-center">
                        <img src="{{ asset($img->imagen) }}" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:8px;">
                        <form action="{{ route('kawsaytemas.galeria.destroy', [$tema, $img]) }}" method="POST" class="mt-1"
                            data-confirm-delete="¿Quitar esta imagen de la galería?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-block">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bv-form-card">
        <div class="bv-form-section-title">Contenido del tema ({{ $tema->archivos->count() }})</div>
        <p class="text-muted small mb-3">Fichas, teoría, videos, presentaciones y demás archivos de este tema. Elige un tipo y sube el archivo — no hace falta volver a elegir el tema.</p>

        <div class="kawsay-tipo-tabs">
            @foreach ($tiposContenido as $tipoSlug => $tipo)
                <button type="button" class="kawsay-tipo-tab {{ $loop->first ? 'is-active' : '' }}" data-tipo-tab="{{ $tipoSlug }}">
                    <i class="fa {{ $tipo['icono'] }}"></i> {{ $tipo['label'] }}
                    <span class="badge badge-light">{{ ($archivosPorTipo[$tipoSlug] ?? collect())->count() }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($tiposContenido as $tipoSlug => $tipo)
            <div class="kawsay-tipo-panel {{ $loop->first ? 'is-active' : '' }}" data-tipo-panel="{{ $tipoSlug }}">
                @if (!empty($archivosPorTipo[$tipoSlug]))
                    <div class="bv-crud-table-wrap mb-3">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach ($archivosPorTipo[$tipoSlug] as $archivo)
                                    <tr class="bv-table-row">
                                        <td>{{ $archivo->titulo }}</td>
                                        <td class="text-muted small">{{ strtoupper($archivo->tipo) }} @if($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif</td>
                                        <td class="text-right" style="width:1%;white-space:nowrap;">
                                            <a href="{{ $archivo->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver en Drive">
                                                <i class="fa fa-external-link"></i>
                                            </a>
                                            <form action="{{ route('kawsayarchivos.destroy', $archivo) }}" method="POST" style="display:inline"
                                                data-confirm-delete="¿Eliminar &quot;{{ $archivo->titulo }}&quot;?"
                                                data-confirm-delete-text="Esta acción no se puede deshacer.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small">Todavía no hay {{ strtolower($tipo['label']) }} en este tema.</p>
                @endif

                <form action="{{ route('kawsayarchivos.store') }}" method="POST" enctype="multipart/form-data" class="form-row align-items-end">
                    @csrf
                    <input type="hidden" name="kawsay_tema_id" value="{{ $tema->id }}">
                    <input type="hidden" name="tipo_contenido" value="{{ $tipoSlug }}">
                    <div class="col-md-5 form-group mb-0">
                        <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Título" required maxlength="255">
                    </div>
                    <div class="col-md-5 form-group mb-0">
                        <input type="file" name="archivo" class="form-control-file" accept="{{ implode(',', array_map(fn($m) => '.' . $m, $tipo['mimes'])) }}" required>
                    </div>
                    <div class="col-md-2 form-group mb-0">
                        <button type="submit" class="btn btn-sm btn-primary btn-block">
                            <i class="fa fa-upload"></i> Subir
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    <div class="bv-form-card">
        <div class="bv-form-section-title">Comentarios ({{ $tema->comentarios->count() }})</div>
        @if ($tema->comentarios->isEmpty())
            <p class="text-muted mb-0">Todavía no hay comentarios públicos en este tema.</p>
        @else
            @foreach ($tema->comentarios as $c)
                <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                    <div>
                        <strong>{{ $c->nombre }}</strong>
                        <span class="text-muted small">{{ $c->created_at->diffForHumans() }}</span>
                        <p class="mb-0">{{ $c->comentario }}</p>
                    </div>
                    <form action="{{ route('kawsaytemas.comentarios.destroy', [$tema, $c]) }}" method="POST"
                        data-confirm-delete="¿Eliminar este comentario?"
                        data-confirm-delete-text="Es de {{ $c->nombre }} y no se puede deshacer.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>

    <style>
        .kawsay-tipo-tabs { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1rem; }
        .kawsay-tipo-tab {
            border: 1px solid #dee2e6; background: #fff; border-radius: 999px;
            padding: .35rem .9rem; font-size: .82rem; color: #495057;
            display: inline-flex; align-items: center; gap: .4rem;
        }
        .kawsay-tipo-tab .badge { background: #f1f3f5; color: #495057; }
        .kawsay-tipo-tab.is-active { background: #343a40; color: #fff; border-color: #343a40; }
        .kawsay-tipo-tab.is-active .badge { background: rgba(255,255,255,.2); color: #fff; }
        .kawsay-tipo-panel { display: none; }
        .kawsay-tipo-panel.is-active { display: block; }
    </style>
    <script>
        document.querySelectorAll('.kawsay-tipo-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var tipo = tab.getAttribute('data-tipo-tab');
                document.querySelectorAll('.kawsay-tipo-tab').forEach(function (t) { t.classList.remove('is-active'); });
                document.querySelectorAll('.kawsay-tipo-panel').forEach(function (p) { p.classList.remove('is-active'); });
                tab.classList.add('is-active');
                document.querySelector('.kawsay-tipo-panel[data-tipo-panel="' + tipo + '"]').classList.add('is-active');
            });
        });
    </script>

@endsection
