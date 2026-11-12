@extends('layouts.app')
@section('titulo', '- Temas Kawsay')


@section('contenido')

    <x-hero-banner titulo="Temas Kawsay" subtitulo="Los temas que se muestran dentro de cada área del repositorio"
        icono="fa-map-signs" color="kawsay">
        <x-slot:stats>
            <x-hero-stat icono="fa-map-signs" :valor="$temas->count()" etiqueta="Temas" />
        </x-slot:stats>
        <x-slot:actions>
            <a href="{{ route('kawsaytemas.create') }}" class="btn btn-sm btn-light">
                <i class="fa fa-plus"></i> Nuevo tema
            </a>
        </x-slot:actions>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
        @if (request('area'))
            <div class="col-md-2 form-group mb-0">
                <a href="{{ route('kawsaytemas.index') }}" class="btn btn-sm btn-outline-secondary btn-block">Limpiar filtro</a>
            </div>
        @endif
    </form>

    <div class="bv-crud-table-wrap">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Portada</th>
                    <th>Área</th>
                    <th>Título</th>
                    <th>Archivos</th>
                    <th>Comentarios</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($temas as $tema)
                    <tr class="bv-table-row">
                        <td>
                            @if ($tema->imagen)
                                <img src="{{ asset($tema->imagen) }}" alt="{{ $tema->titulo }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $tema->area->nombre ?? '—' }}</td>
                        <td>
                            {{ $tema->titulo }}
                            @if ($tema->destacado)
                                <span class="adm-chip adm-chip--accent ml-1"><i class="fa fa-star"></i> Tema del mes</span>
                            @endif
                        </td>
                        <td>{{ $tema->archivos_count }}</td>
                        <td>{{ $tema->comentarios_count }}</td>
                        <td class="text-right bv-crud-actions">
                            {{-- Tema del mes: tarjeta grande en la portada de Kawsay (solo uno a la vez) --}}
                            <form action="{{ route('kawsaytemas.destacar', $tema) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $tema->destacado ? 'btn-warning' : 'btn-outline-secondary' }}"
                                    title="{{ $tema->destacado ? 'Quitar de tema del mes' : 'Destacar como tema del mes en la portada' }}"
                                    aria-pressed="{{ $tema->destacado ? 'true' : 'false' }}">
                                    <i class="fa {{ $tema->destacado ? 'fa-star' : 'fa-star-o' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('kawsay.tema', [$tema->area->slug, $tema->slug]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver en el sitio">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('kawsaytemas.edit', $tema) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <form action="{{ route('kawsaytemas.destroy', $tema) }}" method="POST" style="display:inline"
                                data-confirm-delete="¿Eliminar &quot;{{ $tema->titulo }}&quot;?"
                                data-confirm-delete-text="Se borrará también su galería. Esta acción no se puede deshacer.">
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
                            <i class="fa fa-map-signs bv-empty-icon"></i>
                            Todavía no hay temas creados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
