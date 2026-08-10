@props([
    'titulo',
    'subtitulo' => null,
    'icono' => null,
    'nuevoHref' => null,
    'nuevoLabel' => 'Nuevo',
    'columnas' => [],
    'buscarPlaceholder' => null,
    'vacio' => false,
    'vacioIcono' => 'fa-inbox',
    'vacioMensaje' => 'Aún no hay registros.',
    'vacioAccionHref' => null,
    'vacioAccionLabel' => null,
])

@php
    $idTabla = \Illuminate\Support\Str::slug($titulo) . '-tabla';
@endphp

<div data-animate-stagger>
    <x-page-header :titulo="$titulo" :subtitulo="$subtitulo">
        @if ($nuevoHref)
            <x-slot:actions>
                <a href="{{ $nuevoHref }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus mr-1"></i> {{ $nuevoLabel }}
                </a>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="admin-table-card">
        @if ($buscarPlaceholder)
            <div class="admin-table-card__search">
                <i class="fa fa-search"></i>
                <input type="search" data-table-search="{{ $idTabla }}" placeholder="{{ $buscarPlaceholder }}"
                    aria-label="{{ $buscarPlaceholder }}">
            </div>
        @endif

        @if ($vacio)
            <div class="admin-empty-state">
                <i class="fa {{ $vacioIcono }}"></i>
                <p>{{ $vacioMensaje }}</p>
                @if ($vacioAccionHref)
                    <a href="{{ $vacioAccionHref }}" class="btn btn-primary btn-sm">{{ $vacioAccionLabel }}</a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            @foreach ($columnas as $columna)
                                <th>{{ $columna }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody data-table-body="{{ $idTabla }}">
                        {{ $slot }}
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
