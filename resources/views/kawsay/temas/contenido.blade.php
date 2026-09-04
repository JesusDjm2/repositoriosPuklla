@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $tipo['label'] . ' - ' . $tema->titulo)

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('kawsay.tema', [$areaSlug, $temaSlug]) }}" class="kawsay-back-link">
                    <i class="fa fa-arrow-left"></i> {{ $tema->titulo }}
                </a>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <span class="kawsay-area-header__icon"><i class="fa {{ $tipo['icono'] }}"></i></span>
                    <h1 class="kawsay-area-header__title mb-0">{{ $tipo['label'] }}</h1>
                </div>
                <p class="kawsay-area-header__desc mb-0">{{ $tema->titulo }} · {{ $archivos->count() }} {{ $archivos->count() === 1 ? 'archivo' : 'archivos' }}</p>
            </div>
            <form action="{{ route('busqueda') }}" method="GET" class="kawsay-header-search">
                <input type="text" name="query" placeholder="Buscar..." minlength="3" required>
                <button type="submit" aria-label="Buscar"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    @php
        $formatoIconos = ['pdf' => 'fa-file-pdf', 'imagen' => 'fa-file-image', 'audio' => 'fa-file-audio', 'video' => 'fa-file-video', 'doc' => 'fa-file-word'];
    @endphp
    @forelse ($archivos as $archivo)
        <div class="kawsay-archivo-card">
            <span class="kawsay-archivo-card__icon"><i class="fa {{ $formatoIconos[$archivo->tipo] ?? 'fa-file' }}"></i></span>
            <div class="flex-grow-1 min-w-0">
                <div class="kawsay-archivo-card__title text-truncate">{{ $archivo->titulo }}</div>
                <div class="kawsay-archivo-card__meta">
                    {{ strtoupper($archivo->tipo) }}
                    @if ($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif
                </div>
            </div>
            <a href="{{ \App\Services\GoogleDriveService::getUrl($archivo->drive_id) }}" target="_blank"
                class="btn btn-sm btn-outline-secondary flex-shrink-0">
                <i class="fa fa-eye"></i> Ver
            </a>
            <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id) }}"
                class="btn btn-sm btn-primary flex-shrink-0">
                <i class="fa fa-download"></i> Descargar
            </a>
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="fa {{ $tipo['icono'] }} fa-3x mb-3 d-block opacity-25"></i>
            <p class="mb-0">Todavía no hay {{ strtolower($tipo['label']) }} en este tema.</p>
        </div>
    @endforelse
</div>

@include('kawsay.partials.estilos')
@endsection
