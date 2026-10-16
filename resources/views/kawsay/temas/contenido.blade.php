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
            <form action="{{ route('kawsay.buscar') }}" method="GET" class="kawsay-header-search">
                <input type="text" name="query" placeholder="Buscar en Kawsay..." minlength="3" required aria-label="Buscar en Kawsay">
                <button type="submit" aria-label="Buscar"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    @php
        $formatoIconos = ['pdf' => 'fa-file-pdf', 'imagen' => 'fa-file-image', 'audio' => 'fa-file-audio', 'video' => 'fa-file-video', 'doc' => 'fa-file-word'];
    @endphp
    @php
        $pdfs = $archivos->where('tipo', 'pdf');
        $otros = $archivos->where('tipo', '!=', 'pdf');
    @endphp

    {{-- PDF: tarjeta con portada (primera página) + vista previa en popup, igual que los libros virtuales de la Escuela --}}
    @if ($pdfs->isNotEmpty())
        <div class="row g-3 {{ $otros->isNotEmpty() ? 'mb-4' : '' }}">
            @foreach ($pdfs as $archivo)
                @php
                    $vistaPrevia = "abrirVistaPreviaLibro(" . \Illuminate\Support\Js::from($archivo->drive_id) . ", " . \Illuminate\Support\Js::from($archivo->titulo) . ", 'Abrir en nueva ventana')";
                @endphp
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="lib-book-card">
                        <div class="lib-book-card__cover kawsay-pdf-cover" role="button" tabindex="0"
                            aria-label="Vista previa de {{ $archivo->titulo }}"
                            onclick="{{ $vistaPrevia }}"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();{{ $vistaPrevia }}}">
                            <i class="fa fa-file-pdf kawsay-pdf-cover__ph" aria-hidden="true"></i>
                            <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 600) }}"
                                alt="" loading="lazy" onerror="this.style.display='none'">
                            <div class="lib-book-card__overlay">
                                <span class="lib-book-card__btn"><i class="fa fa-eye"></i> Vista previa</span>
                            </div>
                        </div>
                        <div class="lib-book-card__info">
                            <h3 class="lib-book-card__title" title="{{ $archivo->titulo }}">{{ $archivo->titulo }}</h3>
                            <p class="lib-book-card__author">
                                <i class="fa fa-file-pdf"></i> PDF {{ $archivo->peso_mb ? '· ' . $archivo->peso_mb . ' MB' : '' }}
                            </p>
                            <div class="kawsay-pdf-card__actions">
                                <a href="{{ \App\Services\GoogleDriveService::getUrl($archivo->drive_id) }}" target="_blank"
                                    rel="noopener" title="Abrir en una nueva ventana">
                                    <i class="fa fa-external-link-alt"></i> Nueva ventana
                                </a>
                                <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id) }}"
                                    title="Descargar" aria-label="Descargar">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @foreach ($otros as $archivo)
        <div class="kawsay-archivo-card">
            <span class="kawsay-archivo-card__icon"><i class="fa {{ $formatoIconos[$archivo->tipo] ?? 'fa-file' }}"></i></span>
            <div class="flex-grow-1 min-w-0">
                <div class="kawsay-archivo-card__title text-truncate">{{ $archivo->titulo }}</div>
                <div class="kawsay-archivo-card__meta">
                    {{ strtoupper($archivo->tipo) }}
                    @if ($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif
                </div>
            </div>
            <div class="kawsay-archivo-card__actions">
                @if ($archivo->tipo === 'video')
                    <button type="button" class="btn btn-sm btn-primary"
                        onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('kawsay.archivo.video.stream', $archivo)) }}, {{ \Illuminate\Support\Js::from($archivo->titulo) }}, {{ \Illuminate\Support\Js::from(\App\Services\GoogleDriveService::getUrl($archivo->drive_id)) }})">
                        <i class="fa fa-play"></i> Reproducir
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-primary"
                        onclick="abrirVistaPreviaLibro({{ \Illuminate\Support\Js::from($archivo->drive_id) }}, {{ \Illuminate\Support\Js::from($archivo->titulo) }}, 'Abrir en nueva ventana')">
                        <i class="fa fa-eye"></i> Vista previa
                    </button>
                @endif
                <a href="{{ \App\Services\GoogleDriveService::getUrl($archivo->drive_id) }}" target="_blank" rel="noopener"
                    class="btn btn-sm btn-outline-secondary" title="Abrir en una nueva ventana">
                    <i class="fa fa-external-link-alt"></i> <span class="d-none d-md-inline">Nueva ventana</span>
                </a>
                <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id) }}"
                    class="btn btn-sm btn-outline-secondary" title="Descargar">
                    <i class="fa fa-download"></i> <span class="d-none d-md-inline">Descargar</span>
                </a>
            </div>
        </div>
    @endforeach

    @if ($archivos->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa {{ $tipo['icono'] }} fa-3x mb-3 d-block opacity-25"></i>
            <p class="mb-0">Todavía no hay {{ strtolower($tipo['label']) }} en este tema.</p>
        </div>
    @endif
</div>

{{-- partials.libro-preview-modal ya lo incluye layouts.padre una sola vez --}}
@include('kawsay.partials.estilos')
@endsection
