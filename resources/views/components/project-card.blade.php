@props(['proyecto', 'conteos' => null])

@php
    $conteos = $conteos ?? collect();

    // Mapa slug → nombre de ruta del mini-dashboard existente de cada proyecto.
    // (Fase 2 rediseñará estas vistas; por ahora solo se enlaza a lo que ya existe.)
    $panelRoute = match ($proyecto->slug) {
        'eesp-pukllasunchis' => 'bibliotecario',
        'radio-pukllasunchis' => 'adminSisicha',
        'kawsay' => 'adminKawsay',
        default => null,
    };
@endphp

<div class="project-card">
    <div class="project-card__header">
        <img src="{{ $proyecto->logoUrl() }}" alt="Logo {{ $proyecto->nombre }}" class="project-card__logo">
        <div>
            <div class="project-card__name">{{ $proyecto->nombre }}</div>
            <span class="badge {{ $proyecto->activo ? 'badge-success' : 'badge-secondary' }}">
                {{ $proyecto->activo ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
    </div>

    @if ($conteos->isNotEmpty())
        <div class="project-card__stats">
            @foreach ($conteos->take(4) as $etiqueta => $valor)
                <span class="badge">{{ $etiqueta }}: {{ number_format((int) $valor) }}</span>
            @endforeach
        </div>
    @endif

    <div class="project-card__actions">
        @if ($panelRoute && \Illuminate\Support\Facades\Route::has($panelRoute))
            <a href="{{ route($panelRoute) }}" class="btn btn-sm btn-primary">Ver panel</a>
        @endif
        @role('admin')
            <a href="{{ route('proyectos.edit', $proyecto) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
        @endrole
    </div>
</div>
