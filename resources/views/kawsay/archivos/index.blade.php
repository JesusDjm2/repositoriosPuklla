@extends('layouts.app')
@section('titulo', '- Archivos Kawsay')


@section('contenido')

    <x-hero-banner titulo="Archivos Kawsay" subtitulo="Vista general de fichas, teoría, videos y demás contenido subido"
        icono="fa-folder-open" color="kawsay">
        <x-slot:stats>
            <x-hero-stat icono="fa-file" :valor="$archivos->total()" etiqueta="Archivos subidos" />
        </x-slot:stats>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-info-circle"></i> Para subir un archivo nuevo, hazlo desde la edición del tema al que pertenece — ahí ya no hace falta volver a elegirlo.</span>
        <a href="{{ route('kawsaytemas.index') }}" class="btn btn-sm btn-primary">
            <i class="fa fa-map-signs"></i> Ir a Temas
        </a>
    </div>

    <form method="GET" class="bv-crud-toolbar form-row align-items-end mb-3">
        <div class="col-md-3 form-group mb-0">
            <label class="bv-form-label">Área</label>
            <select name="area" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" {{ (int) request('area') === $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group mb-0">
            <label class="bv-form-label">Tipo de contenido</label>
            <select name="tipo_contenido" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach ($tiposContenido as $tipoSlug => $info)
                    <option value="{{ $tipoSlug }}" {{ request('tipo_contenido') === $tipoSlug ? 'selected' : '' }}>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>
        @if (request('area') || request('tipo_contenido'))
            <div class="col-md-2 form-group mb-0">
                <a href="{{ route('kawsayarchivos.index') }}" class="btn btn-sm btn-outline-secondary btn-block">Limpiar filtros</a>
            </div>
        @endif
    </form>

    <div class="bv-crud-table-wrap">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Título</th>
                    <th>Área</th>
                    <th>Tema</th>
                    <th>Tipo de contenido</th>
                    <th>Peso</th>
                    <th>Subido</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($archivos as $archivo)
                    @php
                        $tipoInfo = $tiposContenido[$archivo->tipo_contenido] ?? null;
                    @endphp
                    <tr class="bv-table-row">
                        <td>{{ $archivo->titulo }}</td>
                        <td>{{ $archivo->tema->area->nombre ?? '—' }}</td>
                        <td>{{ $archivo->tema->titulo ?? '—' }}</td>
                        <td>{{ $tipoInfo['label'] ?? $archivo->tipo_contenido }}</td>
                        <td>{{ $archivo->peso_mb ? $archivo->peso_mb . ' MB' : '—' }}</td>
                        <td>{{ $archivo->created_at->format('d/m/Y') }}</td>
                        <td class="text-right bv-crud-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Vista previa"
                                onclick="abrirVistaPreviaLibro({{ \Illuminate\Support\Js::from($archivo->drive_id) }}, {{ \Illuminate\Support\Js::from($archivo->titulo) }}, 'Abrir en nueva ventana')">
                                <i class="fa fa-eye"></i>
                            </button>
                            <a href="{{ $archivo->getUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Abrir en nueva ventana">
                                <i class="fa fa-external-link"></i>
                            </a>
                            @if ($archivo->tema)
                                <a href="{{ route('kawsaytemas.edit', $archivo->tema) }}" class="btn btn-sm btn-outline-secondary" title="Editar desde el tema">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            @endif
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
                @empty
                    <tr>
                        <td colspan="7" class="bv-empty-state">
                            <i class="fa fa-folder-open bv-empty-icon"></i>
                            Todavía no hay archivos subidos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="bv-crud-pagination">
        {{ $archivos->links() }}
    </div>

    @include('partials.libro-preview-modal')

@endsection
